<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StockOpname;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $reason = $request->string('reason')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $adjustments = StockAdjustment::query()
            ->with(['details', 'creator'])
            ->when($reason !== '',
                fn ($q) => $q->whereHas('details', fn ($d) => $d->where('reason', $reason)))
            ->when($from !== '', fn ($q) => $q->whereDate('adjustment_date', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('adjustment_date', '<=', $to))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('stock_adjustments.index', [
            'adjustments' => $adjustments,
            'reasons' => StockAdjustmentDetail::REASONS,
            'reason' => $reason,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('stock_adjustments.create', [
            'products' => $products,
            'reasons' => StockAdjustmentDetail::WASTE_REASONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'adjustment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty_after' => ['required', 'integer', 'min:0'],
            'items.*.reason' => ['required', 'in:'.implode(',', array_keys(StockAdjustmentDetail::WASTE_REASONS))],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Minimal harus ada 1 produk.',
            'items.*.reason.required' => 'Alasan wajib dipilih untuk setiap produk.',
        ]);

        $adj = DB::transaction(function () use ($data) {
            $adj = StockAdjustment::create([
                'code' => StockAdjustment::generateCode(),
                'adjustment_date' => $data['adjustment_date'],
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
                }
                $before = (int) $product->stock;
                $after = (int) $item['qty_after'];
                $diff = $after - $before;

                if ($diff === 0) {
                    continue; // skip kalau tidak ada perubahan
                }

                // Note wajib jika reason = 'lain'
                if ($item['reason'] === 'lain' && empty($item['note'])) {
                    throw ValidationException::withMessages([
                        'items' => "Produk '{$product->name}': catatan wajib diisi kalau alasan = Lain-lain.",
                    ]);
                }

                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $adj->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'product_code_snapshot' => $product->code,
                    'qty_before' => $before,
                    'qty_after' => $after,
                    'qty_diff' => $diff,
                    'reason' => $item['reason'],
                    'note' => $item['note'] ?? null,
                ]);

                $product->update(['stock' => $after]);
            }

            if ($adj->details()->count() === 0) {
                throw ValidationException::withMessages([
                    'items' => 'Tidak ada produk yang stoknya berubah.',
                ]);
            }

            return $adj;
        });

        return redirect()
            ->route('stock-adjustments.show', $adj)
            ->with('success', "Adjustment {$adj->code} berhasil. {$adj->details()->count()} produk telah diperbarui.");
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $stockAdjustment->load(['details.product', 'creator']);

        return view('stock_adjustments.show', [
            'adjustment' => $stockAdjustment,
            'lockingOpname' => StockOpname::lockingSince($stockAdjustment->adjustment_date),
        ]);
    }

    public function destroy(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        if (! Hash::check($request->input('confirm_password', ''), Auth::user()->password)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        if ($opname = StockOpname::lockingSince($stockAdjustment->adjustment_date)) {
            return back()->with('error', "Tidak bisa dihapus: sudah ada Stock Opname ({$opname->code}, {$opname->opname_date->format('d M Y')}) setelah tanggal waste ini.");
        }

        DB::transaction(function () use ($stockAdjustment) {
            // Kembalikan stok yang sempat terbuang (balik dari qty_diff).
            foreach ($stockAdjustment->details as $detail) {
                $product = Product::lockForUpdate()->find($detail->product_id);
                if ($product) {
                    $product->increment('stock', -1 * (int) $detail->qty_diff);
                }
            }

            $stockAdjustment->delete(); // detail ikut terhapus (cascade)
        });

        return redirect()
            ->route('stock-adjustments.index')
            ->with('success', "Waste {$stockAdjustment->code} berhasil dihapus. Stok produk telah dikembalikan.");
    }
}
