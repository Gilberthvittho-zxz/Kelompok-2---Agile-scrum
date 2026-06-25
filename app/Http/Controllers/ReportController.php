<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use App\Models\SalesTransactionDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Laporan Penjualan: query transaksi penjualan dengan filter rentang tanggal,
     * lengkap dengan detail item per transaksi & rekap produk terjual.
     */
    public function sales(Request $request): View
    {
        // Default rentang tanggal: awal bulan ini s.d. hari ini
        $from = $request->string('from')->toString() ?: now()->startOfMonth()->format('Y-m-d');
        $to   = $request->string('to')->toString() ?: now()->format('Y-m-d');

        // Jaga-jaga: kalau user input tanggal awal > tanggal akhir, otomatis ditukar
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $transactions = SalesTransaction::query()
            ->with(['paymentMethod', 'details', 'creator'])
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->where('status', 'confirmed')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $summary = [
            'total_transaksi' => $transactions->count(),
            'total_omzet'     => (float) $transactions->sum('total'),
            'total_item'      => (int) $transactions->sum(fn ($tx) => $tx->details->sum('qty')),
            'rata_rata'       => $transactions->count() > 0
                ? (float) $transactions->avg(fn ($tx) => (float) $tx->total)
                : 0,
        ];

        // Rekap produk terjual (group per produk) dalam rentang tanggal yang sama
        $productRecap = SalesTransactionDetail::query()
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

        return view('reports.sales', compact('transactions', 'summary', 'productRecap', 'from', 'to'));
    }

    /**
     * Laporan Pembelian: query transaksi pembelian dengan filter rentang tanggal,
     * lengkap dengan detail item, rekap produk dibeli, & rekap per supplier.
     */
    public function purchases(Request $request): View
    {
        $from = $request->string('from')->toString() ?: now()->startOfMonth()->format('Y-m-d');
        $to   = $request->string('to')->toString() ?: now()->format('Y-m-d');

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        // Pending & confirmed dihitung (barang sudah dipesan/tiba), voided tidak
        $purchases = Purchase::query()
            ->with(['supplier', 'details', 'creator'])
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->get();

        $summary = [
            'total_pembelian' => $purchases->count(),
            'total_belanja'   => (float) $purchases->sum('total'),
            'total_item'      => (int) $purchases->sum(fn ($p) => $p->details->sum('qty')),
            'rata_rata'       => $purchases->count() > 0
                ? (float) $purchases->avg(fn ($p) => (float) $p->total)
                : 0,
        ];

        // Rekap produk dibeli (group per produk) dalam rentang tanggal yang sama
        $productRecap = PurchaseDetail::query()
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

        // Rekap belanja per supplier dalam rentang tanggal yang sama
        $supplierRecap = Purchase::query()
            ->select('supplier_id', DB::raw('COUNT(*) as total_pembelian'), DB::raw('SUM(total) as total_belanja'))
            ->with('supplier')
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to)
            ->whereIn('status', ['pending', 'confirmed'])
            ->groupBy('supplier_id')
            ->orderByDesc('total_belanja')
            ->get();

        return view('reports.purchases', compact('purchases', 'summary', 'productRecap', 'supplierRecap', 'from', 'to'));
    }
}