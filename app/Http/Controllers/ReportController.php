<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCsv;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use HandlesCsv;

    /**
     * Hub laporan: halaman berisi kartu menuju tiap jenis laporan.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Laporan Penjualan.
     */
    public function sales(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('reports.sales', array_merge(
            ['from' => $from, 'to' => $to],
            $this->salesData($from, $to),
        ));
    }

    /**
     * Laporan Pembelian.
     */
    public function purchases(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('reports.purchases', array_merge(
            ['from' => $from, 'to' => $to],
            $this->purchasesData($from, $to),
        ));
    }

    /**
     * Laporan Waste (barang terbuang).
     */
    public function waste(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('reports.waste', array_merge(
            ['from' => $from, 'to' => $to, 'reasons' => StockAdjustmentDetail::REASONS],
            $this->wasteData($from, $to),
        ));
    }

    /**
     * Export Laporan Penjualan ke CSV (per transaksi).
     */
    public function salesExport(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $d = $this->salesData($from, $to);

        $rows = $d['transactions']->map(fn ($tx) => [
            $tx->code,
            $tx->transaction_date->format('Y-m-d H:i'),
            $tx->customer_name ?: '-',
            $tx->totalItems(),
            $tx->paymentMethod->name,
            (int) $tx->discount,
            (int) $tx->total,
        ]);

        return $this->streamCsv(
            "laporan-penjualan_{$from}_sd_{$to}.xlsx",
            ['Kode', 'Tanggal', 'Pelanggan', 'Jumlah Item', 'Metode Bayar', 'Diskon', 'Total'],
            $rows,
        );
    }

    /**
     * Export Laporan Pembelian ke CSV (per transaksi).
     */
    public function purchasesExport(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $d = $this->purchasesData($from, $to);

        $rows = $d['purchases']->map(fn ($p) => [
            $p->code,
            $p->purchase_date->format('Y-m-d'),
            $p->supplier->name,
            $p->invoice_number ?: '-',
            $p->totalItems(),
            $p->isPending() ? 'Pending' : 'Confirmed',
            (int) $p->total,
        ]);

        return $this->streamCsv(
            "laporan-pembelian_{$from}_sd_{$to}.xlsx",
            ['Kode', 'Tgl Beli', 'Supplier', 'Invoice', 'Jumlah Item', 'Status', 'Total'],
            $rows,
        );
    }

    /**
     * Export Laporan Waste ke CSV (per item terbuang).
     */
    public function wasteExport(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $d = $this->wasteData($from, $to);

        $rows = [];
        foreach ($d['adjustments'] as $adj) {
            foreach ($adj->details as $det) {
                if ($det->qty_diff >= 0) {
                    continue;
                }
                $rows[] = [
                    $adj->code,
                    $adj->adjustment_date->format('Y-m-d H:i'),
                    $det->product_name_snapshot,
                    $det->product_code_snapshot,
                    $det->qty_before,
                    $det->qty_after,
                    -$det->qty_diff,
                    $det->reasonLabel(),
                    $det->note ?: '-',
                ];
            }
        }

        return $this->streamCsv(
            "laporan-waste_{$from}_sd_{$to}.xlsx",
            ['Kode', 'Tanggal', 'Produk', 'Kode Produk', 'Stok Sebelum', 'Stok Sesudah', 'Terbuang', 'Alasan', 'Catatan'],
            $rows,
        );
    }

    /**
     * Normalisasi rentang tanggal: default awal bulan s.d. hari ini,
     * tukar otomatis jika awal > akhir.
     *
     * @return array{0:string,1:string}
     */
    private function range(Request $request): array
    {
        $from = $request->string('from')->toString() ?: now()->startOfMonth()->format('Y-m-d');
        $to   = $request->string('to')->toString() ?: now()->format('Y-m-d');

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    /**
     * Data laporan penjualan untuk rentang tanggal tertentu.
     */
    private function salesData(string $from, string $to): array
    {
        $transactions = SalesTransaction::query()
            ->with(['paymentMethod', 'details', 'creator'])
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->where('status', 'confirmed')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $salesSummary = [
            'total_transaksi' => $transactions->count(),
            'total_omzet'     => (float) $transactions->sum('total'),
            'total_item'      => (int) $transactions->sum(fn ($tx) => $tx->details->sum('qty')),
            'rata_rata'       => $transactions->count() > 0
                ? (float) $transactions->avg(fn ($tx) => (float) $tx->total)
                : 0,
        ];

        $salesProductRecap = SalesTransactionDetail::query()
            ->select(
                'product_id',
                'product_name_snapshot',
                'product_code_snapshot',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_subtotal')
            )
            ->whereHas('transaction', function ($q) use ($from, $to) {
                $q->whereDate('transaction_date', '>=', $from)
                  ->whereDate('transaction_date', '<=', $to)
                  ->where('status', 'confirmed');
            })
            ->groupBy('product_id', 'product_name_snapshot', 'product_code_snapshot')
            ->orderByDesc('total_qty')
            ->get();

        $paymentRecap = SalesTransaction::query()
            ->select('payment_method_id', DB::raw('COUNT(*) as total_transaksi'), DB::raw('SUM(total) as total_omzet'))
            ->with('paymentMethod')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->where('status', 'confirmed')
            ->groupBy('payment_method_id')
            ->orderByDesc('total_omzet')
            ->get();

        return compact('transactions', 'salesSummary', 'salesProductRecap', 'paymentRecap');
    }

    /**
     * Data laporan pembelian untuk rentang tanggal tertentu.
     */
    private function purchasesData(string $from, string $to): array
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'details', 'creator'])
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->get();

        $purchaseSummary = [
            'total_pembelian' => $purchases->count(),
            'total_belanja'   => (float) $purchases->sum('total'),
            'total_item'      => (int) $purchases->sum(fn ($p) => $p->details->sum('qty')),
            'rata_rata'       => $purchases->count() > 0
                ? (float) $purchases->avg(fn ($p) => (float) $p->total)
                : 0,
        ];

        $purchaseProductRecap = PurchaseDetail::query()
            ->select(
                'product_id',
                'product_name_snapshot',
                'product_code_snapshot',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_subtotal')
            )
            ->whereHas('purchase', function ($q) use ($from, $to) {
                $q->whereDate('purchase_date', '>=', $from)
                  ->whereDate('purchase_date', '<=', $to)
                  ->whereIn('status', ['pending', 'confirmed']);
            })
            ->groupBy('product_id', 'product_name_snapshot', 'product_code_snapshot')
            ->orderByDesc('total_qty')
            ->get();

        $supplierRecap = Purchase::query()
            ->select('supplier_id', DB::raw('COUNT(*) as total_pembelian'), DB::raw('SUM(total) as total_belanja'))
            ->with('supplier')
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to)
            ->whereIn('status', ['pending', 'confirmed'])
            ->groupBy('supplier_id')
            ->orderByDesc('total_belanja')
            ->get();

        return compact('purchases', 'purchaseSummary', 'purchaseProductRecap', 'supplierRecap');
    }

    /**
     * Data laporan waste (barang terbuang) untuk rentang tanggal tertentu.
     * Memakai data Stock Adjustment (qty_diff negatif = jumlah terbuang).
     */
    private function wasteData(string $from, string $to): array
    {
        $inRange = function ($q) use ($from, $to) {
            $q->whereDate('adjustment_date', '>=', $from)
              ->whereDate('adjustment_date', '<=', $to);
        };

        // Jumlah terbuang = nilai absolut dari qty_diff yang negatif
        $wasteQty = 'SUM(CASE WHEN qty_diff < 0 THEN -qty_diff ELSE 0 END)';

        $adjustments = StockAdjustment::query()
            ->with(['details', 'creator'])
            ->where($inRange)
            ->orderBy('adjustment_date')
            ->orderBy('id')
            ->get();

        $wasteProductRecap = StockAdjustmentDetail::query()
            ->select(
                'product_id',
                'product_name_snapshot',
                'product_code_snapshot',
                DB::raw("$wasteQty as total_qty"),
                DB::raw('COUNT(*) as total_kejadian')
            )
            ->whereHas('adjustment', $inRange)
            ->groupBy('product_id', 'product_name_snapshot', 'product_code_snapshot')
            ->orderByDesc('total_qty')
            ->get();

        $wasteReasonRecap = StockAdjustmentDetail::query()
            ->select(
                'reason',
                DB::raw('COUNT(*) as total_kejadian'),
                DB::raw("$wasteQty as total_qty")
            )
            ->whereHas('adjustment', $inRange)
            ->groupBy('reason')
            ->orderByDesc('total_qty')
            ->get();

        $wasteSummary = [
            'total_catatan' => $adjustments->count(),
            'total_item'    => (int) $wasteReasonRecap->sum('total_qty'),
            'jenis_alasan'  => $wasteReasonRecap->count(),
            'produk_kena'   => $wasteProductRecap->count(),
        ];

        return compact('adjustments', 'wasteSummary', 'wasteProductRecap', 'wasteReasonRecap');
    }
}
