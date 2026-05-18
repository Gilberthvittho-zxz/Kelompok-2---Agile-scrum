<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $suppliers = Supplier::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('contact_person', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'q'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Supplier::create($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->loadCount('products');

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $this->validateData($request);

        $supplier->update($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Request $request, Supplier $supplier): RedirectResponse
    {
        if (! $this->passwordOk($request)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    public function toggleStatus(Request $request, Supplier $supplier): RedirectResponse
    {
        if (! $this->passwordOk($request)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        $supplier->update(['is_active' => ! $supplier->is_active]);

        return back()->with('success', "Supplier {$supplier->name} sekarang " . ($supplier->is_active ? 'AKTIF' : 'NON-AKTIF') . '.');
    }

    private function passwordOk(Request $request): bool
    {
        return Hash::check($request->input('confirm_password', ''), Auth::user()->password);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', $request->isMethod('post'));

        return $data;
    }
}
