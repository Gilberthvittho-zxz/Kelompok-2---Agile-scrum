<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\StockOpname;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();
        $supplierId = $request->integer('supplier_id');
        $status = $request->string('status')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $purchases = Purchase::query()
            ->with(['supplier', 'details', 'creator', 'voider'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('invoice_number', 'like', "%{$q}%");
                });
            })
            ->when($supplierId > 0, fn ($query) => $query->where('supplier_id', $supplierId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($from !== '', fn ($query) => $query->whereDate('purchase_date', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('purchase_date', '<=', $to))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        $summary = [
            'today_count' => Purchase::whereDate('purchase_date', today())->whereIn('status', ['confirmed','pending'])->count(),
            'month_total' => (float) Purchase::whereYear('purchase_date', now()->year)
                ->whereMonth('purchase_date', now()->month)
                ->whereIn('status', ['confirmed','pending'])->sum('total'),
            'pending_count' => Purchase::where('status', 'pending')->count(),
            'voided_count' => Purchase::where('status', 'voided')->count(),
        ];

        return view('purchases.index', compact('purchases', 'suppliers', 'q', 'supplierId', 'status', 'from', 'to', 'summary'));
    }

    public function create(Request $request): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.create', compact('suppliers', 'categories', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'arrival_date' => ['required', 'date', 'after_or_equal:purchase_date'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'update_purchase_price' => ['nullable', 'boolean'],
        ], [
            'arrival_date.after_or_equal' => 'Tanggal tiba tidak boleh lebih awal dari tanggal pembelian.',
            'items.required' => 'Minimal harus ada 1 produk.',
            'items.min' => 'Minimal harus ada 1 produk.',
        ]);

        $updatePrice = $request->boolean('update_purchase_price');
        $arrivalDate = \Carbon\Carbon::parse($data['arrival_date'])->startOfDay();
        $alreadyArrived = $arrivalDate->lte(today()); // true = stok langsung masuk

        $purchase = DB::transaction(function () use ($data, $updatePrice, $alreadyArrived) {
            $subtotal = 0;
            $rows = [];

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
                }
                if (! $product->is_active) {
                    throw ValidationException::withMessages(['items' => "Produk '{$product->name}' sedang non-aktif."]);
                }

                // Wajib: produk harus dari supplier yang dipilih
                if ((int) $product->supplier_id !== (int) $data['supplier_id']) {
                    throw ValidationException::withMessages([
                        'items' => "Produk '{$product->name}' bukan dari supplier yang dipilih. 1 transaksi pembelian hanya untuk 1 supplier.",
                    ]);
                }

                $lineSub = $item['qty'] * $item['price'];
                $subtotal += $lineSub;

                $rows[] = [
                    'product' => $product,
                    'qty' => (int) $item['qty'],
                    'price' => (float) $item['price'],
                    'subtotal' => $lineSub,
                ];
            }

            $purchase = Purchase::create([
                'code' => Purchase::generateCode(),
                'purchase_date' => $data['purchase_date'],
                'arrival_date' => $data['arrival_date'],
                'arrived_at' => $alreadyArrived ? now() : null,
                'arrived_by' => $alreadyArrived ? Auth::id() : null,
                'supplier_id' => $data['supplier_id'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => $alreadyArrived ? 'confirmed' : 'pending',
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($rows as $r) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $r['product']->id,
                    'product_name_snapshot' => $r['product']->name,
                    'product_code_snapshot' => $r['product']->code,
                    'qty' => $r['qty'],
                    'price' => $r['price'],
                    'subtotal' => $r['subtotal'],
                ]);

                // Add stok HANYA kalau sudah tiba
                if ($alreadyArrived) {
                    $r['product']->increment('stock', $r['qty']);
                }

                // Update harga beli master kalau diminta (berlaku langsung)
                if ($updatePrice) {
                    $r['product']->update(['purchase_price' => $r['price']]);
                }
            }

            return $purchase;
        });

        $msg = $alreadyArrived
            ? "Pembelian {$purchase->code} berhasil dicatat. Stok produk sudah bertambah."
            : "Pembelian {$purchase->code} berhasil dicatat dengan status PENDING. Stok akan bertambah setelah barang tiba pada {$purchase->arrival_date->format('d M Y')}.";

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', $msg);
    }

    public function confirmArrival(Request $request, Purchase $purchase): RedirectResponse
    {
        if (! $purchase->isPending()) {
            return back()->with('error', 'Pembelian ini sudah confirmed atau voided.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->details as $detail) {
                $product = Product::lockForUpdate()->find($detail->product_id);
                if ($product) {
                    $product->increment('stock', $detail->qty);
                }
            }

            $purchase->update([
                'status' => 'confirmed',
                'arrived_at' => now(),
                'arrived_by' => Auth::id(),
            ]);
        });

        return back()->with('success', "Kedatangan {$purchase->code} dikonfirmasi. Stok produk sudah bertambah.");
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'details.product', 'creator', 'voider']);

        return view('purchases.show', [
            'purchase' => $purchase,
            'lockingOpname' => StockOpname::lockingSince($purchase->purchase_date),
        ]);
    }

    public function void(Request $request, Purchase $purchase): RedirectResponse
    {
        if ($purchase->isVoided()) {
            return back()->with('error', 'Pembelian sudah pernah di-void sebelumnya.');
        }

        if ($opname = StockOpname::lockingSince($purchase->purchase_date)) {
            return back()->with('error', "Tidak bisa void: sudah ada Stock Opname ({$opname->code}, {$opname->opname_date->format('d M Y')}) setelah tanggal pembelian ini. Stok sudah dihitung ulang, jadi pembelian ini terkunci.");
        }

        $data = $request->validate([
            'confirm_password' => ['required', 'string'],
            'void_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! Hash::check($data['confirm_password'], Auth::user()->password)) {
            return back()->with('error', 'Password salah. Void dibatalkan.');
        }

        $wasConfirmed = $purchase->isConfirmed();

        // Kalau status = confirmed → stok sudah ditambah, harus dikurangi balik (validasi cukup)
        // Kalau status = pending → stok belum ditambah, langsung void tanpa adjust stok
        DB::transaction(function () use ($purchase, $data, $wasConfirmed) {
            if ($wasConfirmed) {
                foreach ($purchase->details as $detail) {
                    $product = Product::lockForUpdate()->find($detail->product_id);
                    if ($product && $product->stock < $detail->qty) {
                        throw ValidationException::withMessages([
                            'void' => "Tidak bisa void: stok produk '{$product->name}' sudah berkurang (tersedia {$product->stock}, perlu dikurangi {$detail->qty}). Produk mungkin sudah terjual.",
                        ]);
                    }
                }

                foreach ($purchase->details as $detail) {
                    $product = Product::lockForUpdate()->find($detail->product_id);
                    if ($product) {
                        $product->decrement('stock', $detail->qty);
                    }
                }
            }

            $purchase->update([
                'status' => 'voided',
                'void_reason' => $data['void_reason'],
                'voided_at' => now(),
                'voided_by' => Auth::id(),
            ]);
        });

        $msg = "Pembelian {$purchase->code} berhasil di-void."
            . ($wasConfirmed ? ' Stok telah disesuaikan kembali.' : ' Stok tidak diubah (status sebelumnya pending).');

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', $msg);
    }
}
