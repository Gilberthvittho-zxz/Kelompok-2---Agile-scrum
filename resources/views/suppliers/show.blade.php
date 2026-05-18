@extends('layouts.app')
@section('title', 'Detail Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-truck"></i> Detail Supplier</h5>
        <small class="text-muted">Informasi lengkap pemasok sparepart.</small>
    </div>
    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h5 class="fw-bold mb-1">{{ $supplier->name }}</h5>
                @if($supplier->is_active)
                    <span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Aktif</span>
                @else
                    <span class="badge badge-soft-danger"><i class="bi bi-pause-circle"></i> Non-aktif</span>
                @endif
            </div>
            <div class="text-end">
                <small class="text-muted">Produk Disuplai</small>
                <div><span class="badge bg-secondary">{{ $supplier->products_count }}</span></div>
            </div>
        </div>

        <hr class="my-3">

        <dl class="row mb-0 small">
            <dt class="col-sm-3 text-muted fw-normal">PIC / Kontak</dt>
            <dd class="col-sm-9 mb-2">{{ $supplier->contact_person ?: '—' }}</dd>

            <dt class="col-sm-3 text-muted fw-normal">Email</dt>
            <dd class="col-sm-9 mb-2">
                @if($supplier->email)
                    <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                @else — @endif
            </dd>

            <dt class="col-sm-3 text-muted fw-normal">Telepon</dt>
            <dd class="col-sm-9 mb-2">{{ $supplier->phone ?: '—' }}</dd>

            <dt class="col-sm-3 text-muted fw-normal">Alamat</dt>
            <dd class="col-sm-9 mb-2">{{ $supplier->address ?: '—' }}</dd>
        </dl>

        <div class="d-flex justify-content-between small text-muted">
            <span><i class="bi bi-plus-circle"></i> Dibuat {{ $supplier->created_at->format('d M Y H:i') }}</span>
            <span><i class="bi bi-pencil"></i> Diubah {{ $supplier->updated_at->format('d M Y H:i') }}</span>
        </div>
    </div>

    <div class="action-footer">
        <div class="status-inline">
            <span class="label-status">Status supplier:</span>
            <span class="switch-sm" role="button" tabindex="0"
                  data-confirm-action="{{ route('suppliers.toggle-status', $supplier) }}"
                  data-confirm-method="PATCH"
                  data-confirm-title="{{ $supplier->is_active ? 'Non-aktifkan' : 'Aktifkan' }} Supplier"
                  data-confirm-message="Masukkan password untuk {{ $supplier->is_active ? 'non-aktifkan' : 'aktifkan' }} supplier {{ $supplier->name }}."
                  data-confirm-submit="Konfirmasi"
                  data-confirm-class="{{ $supplier->is_active ? 'btn-warning' : 'btn-success' }}">
                <input type="checkbox" {{ $supplier->is_active ? 'checked' : '' }} aria-hidden="true" tabindex="-1">
                <span class="slider"></span>
            </span>
            <span class="value-status {{ $supplier->is_active ? 'text-success' : 'text-danger' }}">
                {{ $supplier->is_active ? 'Aktif' : 'Non-aktif' }}
            </span>
        </div>

        <div class="actions">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    data-confirm-action="{{ route('suppliers.destroy', $supplier) }}"
                    data-confirm-method="DELETE"
                    data-confirm-title="Hapus Supplier"
                    data-confirm-message="Hapus supplier {{ $supplier->name }}? Masukkan password untuk konfirmasi."
                    data-confirm-submit="Hapus"
                    data-confirm-class="btn-danger">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

@include('partials.password-modal')
@endsection
