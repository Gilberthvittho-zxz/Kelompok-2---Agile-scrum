<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCsv;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    use HandlesCsv;

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

        if ($supplier->products()->exists()) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena masih dipakai oleh produk.');
        }

        if (\App\Models\Purchase::where('supplier_id', $supplier->id)->exists()) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena sudah ada transaksi pembelian.');
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

    /**
     * Download template CSV untuk import supplier.
     */
    public function template(): StreamedResponse
    {
        return $this->streamCsv('template-supplier.xlsx',
            ['name', 'contact_person', 'email', 'phone', 'address'],
            [['Contoh Supplier', 'Budi', 'budi@email.com', '08123456789', 'Jl. Contoh No. 1']],
        );
    }

    /**
     * Import supplier dari file CSV (upsert berdasarkan nama).
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
            $supplier = Supplier::updateOrCreate(
                ['name' => $name],
                [
                    'contact_person' => $row[1] ?? null,
                    'email' => $row[2] ?? null,
                    'phone' => $row[3] ?? null,
                    'address' => $row[4] ?? null,
                ],
            );
            $supplier->wasRecentlyCreated ? $created++ : $updated++;
        }

        return back()->with('success', "Import selesai: {$created} ditambah, {$updated} diperbarui".($skipped ? ", {$skipped} dilewati" : '').'.');
    }
}
