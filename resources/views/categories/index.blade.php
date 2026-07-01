@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><i class="bi bi-tags"></i> Kategori Sparepart</h3>
        <small class="text-muted">Atur kelompok produk agar inventori terorganisir.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('categories.template') }}" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Template</a>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload"></i> Import</button>
        <a href="{{ route('categories.create') }}" class="btn btn-dark">
            <i class="bi bi-plus-lg"></i> Tambah Kategori
        </a>
    </div>
</div>

@include('partials.import-modal', [
    'entity' => 'Kategori',
    'importRoute' => route('categories.import'),
    'templateRoute' => route('categories.template'),
    'columns' => 'name, description',
])

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari kategori..." class="form-control">
                    @if($q)
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah Produk</th>
                        <th class="text-center">Status</th>
                        <th width="80" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $i => $cat)
                        <tr class="{{ ! $cat->is_active ? 'table-secondary' : '' }}">
                            <td>{{ $categories->firstItem() + $i }}</td>
                            <td><strong>{{ $cat->name }}</strong></td>
                            <td><span class="text-muted">{{ $cat->description ?: '—' }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $cat->products_count }}</span>
                            </td>
                            <td class="text-center">
                                @if($cat->is_active)
                                    <span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Aktif</span>
                                @else
                                    <span class="badge badge-soft-danger"><i class="bi bi-pause-circle"></i> Non-aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('categories.show', $cat) }}" class="btn btn-sm btn-outline-secondary" title="Lihat detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $categories->links() }}</div>
    </div>
</div>
@endsection
