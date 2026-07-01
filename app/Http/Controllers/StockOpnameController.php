<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\SalesTransaction;
use App\Models\StockAdjustment;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $opnames = StockOpname::query()
            ->with(['details', 'creator'])
            ->when($from !== '', fn ($q) => $q->whereDate('opname_date', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('opname_date', '<=', $to))
            ->orderByDesc('opname_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('stock_opnames.index', [
            'opnames' => $opnames,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('stock_opnames.create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'opname_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty_physical' => ['nullable', 'integer', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Minimal harus ada 1 produk yang dihitung.',
        ]);

        $opname = DB::transaction(function () use ($data) {
            $opname = StockOpname::create([
                'code' => StockOpname::generateCode(),
                'opname_date' => $data['opname_date'],
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
                }
                $system = (int) $product->stock;
                // Stok fisik kosong dianggap 0.
                $physical = (isset($item['qty_physical']) && $item['qty_physical'] !== '' && $item['qty_physical'] !== null)
                    ? (int) $item['qty_physical']
                    : 0;
                $diff = $physical - $system;

                if ($diff === 0) {
                    continue; // stok fisik sama dengan sistem → tidak dicatat
                }

                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'product_code_snapshot' => $product->code,
                    'qty_system' => $system,
                    'qty_physical' => $physical,
                    'qty_diff' => $diff,
                    'note' => $item['note'] ?? null,
                ]);

                $product->update(['stock' => $physical]);
            }

            return $opname;
        });

        $jumlah = $opname->details()->count();
        $pesan = $jumlah > 0
            ? "Opname {$opname->code} berhasil. {$jumlah} produk disesuaikan."
            : "Opname {$opname->code} berhasil dicatat. Semua stok fisik cocok dengan sistem (tidak ada selisih).";

        return redirect()
            ->route('stock-opnames.show', $opname)
            ->with('success', $pesan);
    }

    public function show(StockOpname $stockOpname): View
    {
        $stockOpname->load(['details.product', 'creator']);

        return view('stock_opnames.show', [
            'opname' => $stockOpname,
            'laterActivity' => $this->laterActivity($stockOpname),
        ]);
    }

    public function destroy(Request $request, StockOpname $stockOpname): RedirectResponse
    {
        if (! Hash::check($request->input('confirm_password', ''), Auth::user()->password)) {
            return back()->with('error', 'Password salah. Aksi dibatalkan.');
        }

        if ($later = $this->laterActivity($stockOpname)) {
            return back()->with('error', "Tidak bisa dihapus: sudah ada {$later} setelah opname ini.");
        }

        DB::transaction(function () use ($stockOpname) {
            // Kembalikan stok tiap produk ke kondisi sebelum opname.
            foreach ($stockOpname->details as $detail) {
                $product = Product::lockForUpdate()->find($detail->product_id);
                if ($product) {
                    $product->update(['stock' => (int) $detail->qty_system]);
                }
            }

            $stockOpname->delete(); // detail ikut terhapus (cascade)
        });

        return redirect()
            ->route('stock-opnames.index')
            ->with('success', "Opname {$stockOpname->code} dihapus. Stok dikembalikan ke kondisi sebelum opname.");
    }

    /**
     * Deskripsi aktivitas (penjualan/pembelian/waste) yang terjadi SETELAH opname.
     * Kalau ada, opname tidak boleh dihapus. Mengembalikan null jika aman.
     */
    private function laterActivity(StockOpname $opname): ?string
    {
        $date = $opname->opname_date;

        if ($t = SalesTransaction::where('transaction_date', '>', $date)->orderBy('transaction_date')->first()) {
            return "transaksi penjualan {$t->code} ({$t->transaction_date->format('d M Y')})";
        }
        if ($t = Purchase::where('purchase_date', '>', $date)->orderBy('purchase_date')->first()) {
            return "transaksi pembelian {$t->code} ({$t->purchase_date->format('d M Y')})";
        }
        if ($t = StockAdjustment::where('adjustment_date', '>', $date)->orderBy('adjustment_date')->first()) {
            return "waste {$t->code} ({$t->adjustment_date->format('d M Y')})";
        }

        return null;
    }
}
