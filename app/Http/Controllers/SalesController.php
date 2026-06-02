<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $transactions = SalesTransaction::query()
            ->with(['paymentMethod', 'details', 'creator', 'voider'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('customer_name', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($from !== '', fn ($query) => $query->whereDate('transaction_date', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('transaction_date', '<=', $to))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'today_count' => SalesTransaction::whereDate('transaction_date', today())->where('status', 'confirmed')->count(),
            'today_revenue' => (float) SalesTransaction::whereDate('transaction_date', today())->where('status', 'confirmed')->sum('total'),
            'month_revenue' => (float) SalesTransaction::whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->where('status', 'confirmed')->sum('total'),
            'voided_count' => SalesTransaction::where('status', 'voided')->count(),
        ];

        return view('sales.index', compact('transactions', 'q', 'status', 'from', 'to', 'summary'));
    }

    public function create(Request $request): View
    {
        $categoryId = $request->integer('category_id');
        $q = $request->string('q')->toString();

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(60)
            ->get();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('sales.create', compact('products', 'categories', 'paymentMethods', 'categoryId', 'q'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Minimal harus ada 1 produk di keranjang.',
            'items.min' => 'Minimal harus ada 1 produk di keranjang.',
        ]);

        $discount = (float) ($data['discount'] ?? 0);

        try {
            $transaction = DB::transaction(function () use ($data, $discount, $request) {
                $subtotal = 0;
                $items = [];

                // Lock produk untuk hindari race condition
                foreach ($data['items'] as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    if (! $product) {
                        throw ValidationException::withMessages(['items' => "Produk tidak ditemukan."]);
                    }
                    if (! $product->is_active) {
                        throw ValidationException::withMessages(['items' => "Produk '{$product->name}' sedang non-aktif."]);
                    }
                    if ($product->stock < $item['qty']) {
                        throw ValidationException::withMessages([
                            'items' => "Stok produk '{$product->name}' tidak mencukupi (tersedia {$product->stock}, diminta {$item['qty']}).",
                        ]);
                    }

                    $lineSubtotal = $product->selling_price * $item['qty'];
                    $subtotal += $lineSubtotal;

                    $items[] = [
                        'product' => $product,
                        'qty' => $item['qty'],
                        'price' => $product->selling_price,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $total = max(0, $subtotal - $discount);

                if ($data['paid_amount'] < $total) {
                    throw ValidationException::withMessages([
                        'paid_amount' => "Jumlah dibayar (Rp ".number_format($data['paid_amount'], 0, ',', '.').") kurang dari total (Rp ".number_format($total, 0, ',', '.').").",
                    ]);
                }

                $tx = SalesTransaction::create([
                    'code' => SalesTransaction::generateCode(),
                    'transaction_date' => now(),
                    'payment_method_id' => $data['payment_method_id'],
                    'customer_name' => $data['customer_name'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'paid_amount' => $data['paid_amount'],
                    'change_amount' => $data['paid_amount'] - $total,
                    'status' => 'confirmed',
                    'note' => $data['note'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                foreach ($items as $it) {
                    SalesTransactionDetail::create([
                        'sales_transaction_id' => $tx->id,
                        'product_id' => $it['product']->id,
                        'product_name_snapshot' => $it['product']->name,
                        'product_code_snapshot' => $it['product']->code,
                        'qty' => $it['qty'],
                        'price' => $it['price'],
                        'subtotal' => $it['subtotal'],
                    ]);

                    // Deduct stok
                    $it['product']->decrement('stock', $it['qty']);
                }

                return $tx;
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()
            ->route('sales.show', $transaction)
            ->with('success', "Transaksi {$transaction->code} berhasil disimpan.");
    }

    public function show(SalesTransaction $sale): View
    {
        $sale->load(['paymentMethod', 'details.product', 'creator', 'voider']);

        return view('sales.show', ['sale' => $sale]);
    }

    public function void(Request $request, SalesTransaction $sale): RedirectResponse
    {
        if ($sale->isVoided()) {
            return back()->with('error', 'Transaksi sudah pernah di-void sebelumnya.');
        }

        $data = $request->validate([
            'confirm_password' => ['required', 'string'],
            'void_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'void_reason.required' => 'Alasan void wajib diisi.',
            'void_reason.min' => 'Alasan void minimal 5 karakter.',
        ]);

        if (! Hash::check($data['confirm_password'], Auth::user()->password)) {
            return back()->with('error', 'Password salah. Void dibatalkan.');
        }

        DB::transaction(function () use ($sale, $data) {
            // Restore stok
            foreach ($sale->details as $detail) {
                $product = Product::lockForUpdate()->find($detail->product_id);
                if ($product) {
                    $product->increment('stock', $detail->qty);
                }
            }

            $sale->update([
                'status' => 'voided',
                'void_reason' => $data['void_reason'],
                'voided_at' => now(),
                'voided_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Transaksi {$sale->code} berhasil di-void. Stok produk telah dikembalikan.");
    }
}
