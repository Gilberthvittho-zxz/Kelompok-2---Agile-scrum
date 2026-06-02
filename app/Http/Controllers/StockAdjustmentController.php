<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->when($reason !== '', fn ($q) => $q->where('reason', $reason))
            ->when($from !== '', fn ($q) => $q->whereDate('adjustment_date', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('adjustment_date', '<=', $to))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('stock_adjustments.index', [
            'adjustments' => $adjustments,
            'reasons' => StockAdjustment::REASONS,
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
            'reasons' => StockAdjustment::REASONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'adjustment_date' => ['required', 'date'],
            'reason' => ['required', 'in:'.implode(',', array_keys(StockAdjustment::REASONS))],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty_after' => ['required', 'integer', 'min:0'],
        ], [
            'items.required' => 'Minimal harus ada 1 produk.',
        ]);

        // Note wajib jika reason = 'lain'
        if ($data['reason'] === 'lain' && empty($data['note'])) {
            throw ValidationException::withMessages([
                'note' => 'Catatan wajib diisi jika alasan = Lain-lain.',
            ]);
        }

        $adj = DB::transaction(function () use ($data) {
            $adj = StockAdjustment::create([
                'code' => StockAdjustment::generateCode(),
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'],
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

                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $adj->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'product_code_snapshot' => $product->code,
                    'qty_before' => $before,
                    'qty_after' => $after,
                    'qty_diff' => $diff,
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
            ->with('success', "Adjustment {$adj->code} berhasil. Stok produk telah diperbarui.");
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $stockAdjustment->load(['details.product', 'creator']);

        return view('stock_adjustments.show', [
            'adjustment' => $stockAdjustment,
        ]);
    }
}
