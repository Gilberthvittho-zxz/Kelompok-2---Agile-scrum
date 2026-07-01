<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();

        // ===== Statistik dasar =====
        $stats = [
            'low_stock' => Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count(),
        ];

        // ===== Ringkasan transaksi penjualan hari ini =====
        $todayTransactions = SalesTransaction::query()
            ->whereDate('transaction_date', $today)
            ->where('status', 'confirmed')
            ->get();

        $todaySummary = [
            'revenue'      => (float) $todayTransactions->sum('total'),
            'transactions' => $todayTransactions->count(),
            'items_sold'   => (int) SalesTransactionDetail::whereIn('sales_transaction_id', $todayTransactions->pluck('id'))->sum('qty'),
        ];

        // ===== Grafik pendapatan penjualan 7 hari terakhir =====
        $salesChart = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();

            return [
                'label'   => $date->translatedFormat('D, d M'),
                'revenue' => (float) SalesTransaction::whereDate('transaction_date', $date)
                    ->where('status', 'confirmed')
                    ->sum('total'),
            ];
        })->values();

        // ===== Top 5 produk terlaris hari ini =====
        $topProducts = SalesTransactionDetail::query()
            ->select(
                'product_id',
                'product_name_snapshot',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('transaction', function ($q) use ($today) {
                $q->whereDate('transaction_date', $today)->where('status', 'confirmed');
            })
            ->groupBy('product_id', 'product_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ===== Produk dengan stok menipis (perlu restock) =====
        $lowStockProducts = Product::with('supplier')
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'stats',
            'todaySummary',
            'salesChart',
            'topProducts',
            'lowStockProducts'
        ));
    }
}