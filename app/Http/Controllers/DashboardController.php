<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'categories' => Category::count(),
            'suppliers' => Supplier::count(),
            'products' => Product::count(),
            'low_stock' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
