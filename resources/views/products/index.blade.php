@extends('layouts.app')
@section('title', 'Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0"><i class="bi bi-box-seam"></i> Produk Sparepart</h3>
            <small class="text-muted">Kelola data produk lengkap dengan harga, stok, dan gambar.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.template') }}" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Template</a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload"></i> Import</button>
            <a href="{{ route('products.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-lg"></i> Tambah Produk
            </a>
        </div>
    </div>

    @include('partials.import-modal', [
        'entity' => 'Produk',
        'importRoute' => route('products.import'),
        'templateRoute' => route('products.template'),
        'columns' => 'code, name, description, category, supplier, purchase_price, selling_price, stock, min_stock',
    ])

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / kode produk..."
                            class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">— Semua Kategori —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="supplier_id" class="form-select">
                        <option value="">— Semua Supplier —</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected($supplierId == $sup->id)>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
                </div>
                @if($q || $categoryId)
                    <div class="col-md-2">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th width="70">Gambar</th>
                            <th>Kode / Nama</th>
                            <th>Kategori</th>
                            <th>Supplier</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-end">Harga Jual</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Status</th>
                            <th width="80" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $i => $p)
                            <tr class="{{ !$p->is_active ? 'table-secondary' : '' }}">
                                <td>{{ $products->firstItem() + $i }}</td>
                                <td>
                                    <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="product-thumb">
                                </td>
                                <td>
                                    <div class="small text-muted">{{ $p->code ?: '—' }}</div>
                                    <strong>{{ $p->name }}</strong>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $p->category?->name }}</span></td>
                                <td>{{ $p->supplier?->name ?: '—' }}</td>
                                <td class="text-end">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($p->stock <= 0)
                                        <span class="badge badge-soft-danger">Habis</span>
                                    @elseif($p->isLowStock())
                                        <span class="badge badge-soft-warning">{{ $p->stock }} <small>(menipis)</small></span>
                                    @else
                                        <span class="badge badge-soft-success">{{ $p->stock }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($p->is_active)
                                        <span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Aktif</span>
                                    @else
                                        <span class="badge badge-soft-danger"><i class="bi bi-pause-circle"></i> Non-aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-secondary"
                                        title="Lihat detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Belum ada produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $products->links() }}</div>
        </div>
    </div>
@endsection