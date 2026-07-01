<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCsv;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    use HandlesCsv;

    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $categories = Category::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories', 'q'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category): View
    {
        $category->loadCount('products');

        return view('categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$category->id],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Category $category): RedirectResponse
    {
        if (! $this->passwordOk($request)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', "Kategori {$category->name} sekarang " . ($category->is_active ? 'AKTIF' : 'NON-AKTIF') . '.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if (! $this->passwordOk($request)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        if ($category->products()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih memiliki produk.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    private function passwordOk(Request $request): bool
    {
        return Hash::check($request->input('confirm_password', ''), Auth::user()->password);
    }

    /**
     * Download template CSV untuk import kategori.
     */
    public function template(): StreamedResponse
    {
        return $this->streamCsv('template-kategori.xlsx',
            ['name', 'description'],
            [['Contoh Kategori', 'Deskripsi opsional']],
        );
    }

    /**
     * Import kategori dari file CSV (upsert berdasarkan nama).
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [], ['file' => 'File CSV']);

        $rows = $this->readCsv($request->file('file'));
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $name = trim($row[0] ?? '');
            if ($name === '') {
                $skipped++;
                continue;
            }
            $category = Category::updateOrCreate(
                ['name' => $name],
                ['description' => $row[1] ?? null],
            );
            $category->wasRecentlyCreated ? $created++ : $updated++;
        }

        return back()->with('success', "Import selesai: {$created} ditambah, {$updated} diperbarui".($skipped ? ", {$skipped} dilewati" : '').'.');
    }
}
