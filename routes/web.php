<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    Route::get('categories/template', [CategoryController::class, 'template'])->name('categories.template');
    Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import');
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');

    Route::get('suppliers/template', [SupplierController::class, 'template'])->name('suppliers.template');
    Route::post('suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');
    Route::resource('suppliers', SupplierController::class);
    Route::patch('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])
        ->name('suppliers.toggle-status');

    Route::get('products/template', [ProductController::class, 'template'])->name('products.template');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');

    // Inventory
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');

    // Transaksi Penjualan
    Route::get('sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('sales/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('sales', [SalesController::class, 'store'])->name('sales.store');
    Route::get('sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::patch('sales/{sale}/void', [SalesController::class, 'void'])->name('sales.void');

    // Transaksi Pembelian
    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::patch('purchases/{purchase}/void', [PurchaseController::class, 'void'])->name('purchases.void');
    Route::patch('purchases/{purchase}/confirm-arrival', [PurchaseController::class, 'confirmArrival'])->name('purchases.confirm-arrival');

    // Waste (barang terbuang) — sebelumnya Stock Adjustment
    Route::get('stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
    Route::get('stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    Route::get('stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('stock-adjustments.show');
    Route::delete('stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'destroy'])->name('stock-adjustments.destroy');

    // Stock Opname (hitung stok fisik)
    Route::get('stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
    Route::get('stock-opnames/create', [StockOpnameController::class, 'create'])->name('stock-opnames.create');
    Route::post('stock-opnames', [StockOpnameController::class, 'store'])->name('stock-opnames.store');
    Route::get('stock-opnames/{stockOpname}', [StockOpnameController::class, 'show'])->name('stock-opnames.show');
    Route::delete('stock-opnames/{stockOpname}', [StockOpnameController::class, 'destroy'])->name('stock-opnames.destroy');

    // Laporan
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/sales/export', [ReportController::class, 'salesExport'])->name('reports.sales.export');
    Route::get('reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('reports/purchases/export', [ReportController::class, 'purchasesExport'])->name('reports.purchases.export');
    Route::get('reports/waste', [ReportController::class, 'waste'])->name('reports.waste');
    Route::get('reports/waste/export', [ReportController::class, 'wasteExport'])->name('reports.waste.export');
});
