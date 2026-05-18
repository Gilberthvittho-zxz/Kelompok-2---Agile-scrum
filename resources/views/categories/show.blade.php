@extends('layouts.app')
@section('title', 'Detail Kategori')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-tag"></i> Detail Kategori</h5>
        <small class="text-muted">Informasi lengkap kategori sparepart.</small>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h5 class="fw-bold mb-1">{{ $category->name }}</h5>
                @if($category->is_active)
                    <span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Aktif</span>
                @else
                    <span class="badge badge-soft-danger"><i class="bi bi-pause-circle"></i> Non-aktif</span>
                @endif
            </div>
            <div class="text-end">
                <small class="text-muted">Jumlah Produk</small>
                <div><span class="badge bg-secondary">{{ $category->products_count }}</span></div>
            </div>
        </div>

        <hr class="my-3">

        <dl class="row mb-0 small">
            <dt class="col-sm-3 text-muted fw-normal">Deskripsi</dt>
            <dd class="col-sm-9 mb-2">{{ $category->description ?: '—' }}</dd>
        </dl>

        <div class="d-flex justify-content-between small text-muted">
            <span><i class="bi bi-plus-circle"></i> Dibuat {{ $category->created_at->format('d M Y H:i') }}</span>
            <span><i class="bi bi-pencil"></i> Diubah {{ $category->updated_at->format('d M Y H:i') }}</span>
        </div>
    </div>

    <div class="action-footer">
        <div class="status-inline">
            <span class="label-status">Status kategori:</span>
            <span class="switch-sm" role="button" tabindex="0"
                  data-confirm-action="{{ route('categories.toggle-status', $category) }}"
                  data-confirm-method="PATCH"
                  data-confirm-title="{{ $category->is_active ? 'Non-aktifkan' : 'Aktifkan' }} Kategori"
                  data-confirm-message="Masukkan password untuk {{ $category->is_active ? 'non-aktifkan' : 'aktifkan' }} kategori {{ $category->name }}."
                  data-confirm-submit="Konfirmasi"
                  data-confirm-class="{{ $category->is_active ? 'btn-warning' : 'btn-success' }}">
                <input type="checkbox" {{ $category->is_active ? 'checked' : '' }} aria-hidden="true" tabindex="-1">
                <span class="slider"></span>
            </span>
            <span class="value-status {{ $category->is_active ? 'text-success' : 'text-danger' }}">
                {{ $category->is_active ? 'Aktif' : 'Non-aktif' }}
            </span>
        </div>

        <div class="actions">
            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @if($category->products_count > 0)
                <button type="button" class="btn btn-sm btn-outline-danger" disabled
                        title="Tidak bisa dihapus karena masih memiliki produk">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            @else
                <button type="button" class="btn btn-sm btn-outline-danger"
                        data-confirm-action="{{ route('categories.destroy', $category) }}"
                        data-confirm-method="DELETE"
                        data-confirm-title="Hapus Kategori"
                        data-confirm-message="Hapus kategori {{ $category->name }}? Masukkan password untuk konfirmasi."
                        data-confirm-submit="Hapus"
                        data-confirm-class="btn-danger">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            @endif
        </div>
    </div>
</div>

@if($category->products_count > 0)
    <small class="text-muted d-block mt-2">
        <i class="bi bi-info-circle"></i> Kategori ini tidak bisa dihapus karena masih dipakai oleh {{ $category->products_count }} produk.
    </small>
@endif

@include('partials.password-modal')
@endsection
