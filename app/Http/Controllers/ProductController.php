<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();
        $categoryId = $request->integer('category_id');
        $supplierId = $request->integer('supplier_id'); 

        $products = Product::query()
            ->with(['category', 'supplier'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->when($categoryId > 0, fn($query) => $query->where('category_id', $categoryId))
            ->when($supplierId > 0, fn($query) => $query->where('supplier_id', $supplierId)) 
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get(); 


        return view('products.index', compact('products', 'categories', 'q', 'categoryId', 'suppliers', 'supplierId'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'supplier']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::where(function ($q) use ($product) {
            $q->where('is_active', true)->orWhere('id', $product->category_id);
        })->orderBy('name')->get();

        $suppliers = Supplier::where(function ($q) use ($product) {
            $q->where('is_active', true);
            if ($product->supplier_id) {
                $q->orWhere('id', $product->supplier_id);
            }
        })->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateData($request, $product->id);

        if ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        if (!$this->passwordOk($request)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus (soft delete - histori tetap tersimpan).');
    }

    public function toggleStatus(Request $request, Product $product): RedirectResponse
    {
        if (!$this->passwordOk($request)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        $product->update(['is_active' => !$product->is_active]);

        return back()->with('success', "Produk {$product->name} sekarang " . ($product->is_active ? 'AKTIF' : 'NON-AKTIF') . '.');
    }

    private function passwordOk(Request $request): bool
    {
        return Hash::check($request->input('confirm_password', ''), Auth::user()->password);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $codeRule = 'unique:products,code';
        if ($ignoreId) {
            $codeRule .= ',' . $ignoreId;
        }

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50', $codeRule],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', $request->isMethod('post'));

        return $data;
    }
}
