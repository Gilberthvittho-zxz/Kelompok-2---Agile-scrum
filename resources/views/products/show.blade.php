@extends('layouts.app')
@section('title', 'Detail Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Detail Produk</h5>
        <small class="text-muted">Informasi lengkap produk sparepart.</small>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 text-center">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     class="img-fluid rounded border" style="max-height: 200px; object-fit: contain;">
            </div>
            <div class="col-md-9">
                <div class="small text-muted">{{ $product->code ?: 'Tanpa SKU' }}</div>
                <h5 class="fw-bold mb-2">{{ $product->name }}</h5>

                <div class="mb-3">
                    @if($product->is_active)
                        <span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Aktif</span>
                    @else
                        <span class="badge badge-soft-danger"><i class="bi bi-pause-circle"></i> Non-aktif</span>
                    @endif

                    @if($product->stock <= 0)
                        <span class="badge badge-soft-danger ms-1"><i class="bi bi-x-octagon"></i> Stok Habis</span>
                    @elseif($product->isLowStock())
                        <span class="badge badge-soft-warning ms-1"><i class="bi bi-exclamation-triangle"></i> Stok Menipis</span>
                    @endif
                </div>

                <div class="row small">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted fw-normal">Kategori</dt>
                            <dd class="col-7 mb-1">{{ $product->category?->name ?? '—' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Supplier</dt>
                            <dd class="col-7 mb-1">{{ $product->supplier?->name ?? '—' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Harga Beli</dt>
                            <dd class="col-7 mb-1">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</dd>
                            <dt class="col-5 text-muted fw-normal">Harga Jual</dt>
                            <dd class="col-7 mb-1 fw-semibold">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted fw-normal">Margin</dt>
                            <dd class="col-7 mb-1">
                                Rp {{ number_format($product->selling_price - $product->purchase_price, 0, ',', '.') }}
                                @if($product->purchase_price > 0)
                                    <span class="text-muted">
                                        ({{ number_format((($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100, 1) }}%)
                                    </span>
                                @endif
                            </dd>
                            <dt class="col-5 text-muted fw-normal">Stok Saat Ini</dt>
                            <dd class="col-7 mb-1"><strong>{{ $product->stock }}</strong> pcs</dd>
                            <dt class="col-5 text-muted fw-normal">Stok Minimum</dt>
                            <dd class="col-7 mb-1">{{ $product->min_stock }} pcs</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        @if($product->description)
            <hr class="my-3">
            <div class="small">
                <span class="text-muted">Deskripsi:</span> {{ $product->description }}
            </div>
        @endif

        <div class="d-flex justify-content-between small text-muted mt-3">
            <span><i class="bi bi-plus-circle"></i> Dibuat {{ $product->created_at->format('d M Y H:i') }}</span>
            <span><i class="bi bi-pencil"></i> Diubah {{ $product->updated_at->format('d M Y H:i') }}</span>
        </div>
    </div>

    <div class="action-footer">
        <div class="status-inline">
            <span class="label-status">Status produk:</span>
            <span class="switch-sm" role="button" tabindex="0"
                  data-confirm-action="{{ route('products.toggle-status', $product) }}"
                  data-confirm-method="PATCH"
                  data-confirm-title="{{ $product->is_active ? 'Non-aktifkan' : 'Aktifkan' }} Produk"
                  data-confirm-message="Masukkan password untuk {{ $product->is_active ? 'non-aktifkan' : 'aktifkan' }} produk {{ $product->name }}."
                  data-confirm-submit="Konfirmasi"
                  data-confirm-class="{{ $product->is_active ? 'btn-warning' : 'btn-success' }}">
                <input type="checkbox" {{ $product->is_active ? 'checked' : '' }} aria-hidden="true" tabindex="-1">
                <span class="slider"></span>
            </span>
            <span class="value-status {{ $product->is_active ? 'text-success' : 'text-danger' }}">
                {{ $product->is_active ? 'Aktif' : 'Non-aktif' }}
            </span>
        </div>

        <div class="actions">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    data-confirm-action="{{ route('products.destroy', $product) }}"
                    data-confirm-method="DELETE"
                    data-confirm-title="Hapus Produk"
                    data-confirm-message="Soft-delete produk {{ $product->name }}? Histori tetap tersimpan. Masukkan password untuk konfirmasi."
                    data-confirm-submit="Hapus"
                    data-confirm-class="btn-danger">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

@include('partials.password-modal')
@endsection
