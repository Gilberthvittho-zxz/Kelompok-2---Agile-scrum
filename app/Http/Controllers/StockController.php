<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->integer('category_id');
        $lowOnly = $request->boolean('low_only');

        $products = Product::query()
            ->with('category')
            ->when($categoryId > 0, fn ($q) => $q->where('category_id', $categoryId))
            ->when($lowOnly, fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        $summary = [
            'total_items' => Product::count(),
            'low_stock' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
        ];

        return view('stocks.index', compact('products', 'categories', 'categoryId', 'lowOnly', 'summary'));
    }
}
