@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Detail Transaksi</h5>
        <small class="text-muted">{{ $sale->code }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-printer"></i> Cetak Struk
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body" id="receipt">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="fw-bold mb-1">{{ $sale->code }}</h4>
                        <div class="small text-muted">{{ $sale->transaction_date->format('l, d M Y H:i') }} WIB</div>
                        @if($sale->isVoided())
                            <span class="badge badge-soft-danger mt-1"><i class="bi bi-x-octagon"></i> VOIDED</span>
                        @else
                            <span class="badge badge-soft-success mt-1"><i class="bi bi-check-circle"></i> CONFIRMED</span>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Pelanggan</div>
                        <div class="fw-semibold">{{ $sale->customer_name ?: '— Walk-in —' }}</div>
                        <div class="small text-muted mt-1">Kasir</div>
                        <div class="fw-semibold">{{ $sale->creator?->name ?? '—' }}</div>
                    </div>
                </div>

                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->details as $i => $detail)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <div class="small text-muted">{{ $detail->product_code_snapshot ?: '—' }}</div>
                                    <strong>{{ $detail->product_name_snapshot }}</strong>
                                </td>
                                <td class="text-center">{{ $detail->qty }}</td>
                                <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted fw-normal">Metode Bayar</dt>
                            <dd class="col-7">
                                <i class="bi {{ $sale->paymentMethod->icon }}"></i>
                                {{ $sale->paymentMethod->name }}
                            </dd>
                            <dt class="col-5 text-muted fw-normal">Total Item</dt>
                            <dd class="col-7">{{ $sale->totalItems() }} pcs</dd>
                            @if($sale->note)
                                <dt class="col-5 text-muted fw-normal">Catatan</dt>
                                <dd class="col-7">{{ $sale->note }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row small mb-0">
                            <dt class="col-6 text-muted fw-normal text-end">Subtotal</dt>
                            <dd class="col-6 text-end">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</dd>
                            <dt class="col-6 text-muted fw-normal text-end">Diskon</dt>
                            <dd class="col-6 text-end">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</dd>
                            <dt class="col-6 fw-bold text-end">TOTAL</dt>
                            <dd class="col-6 text-end fw-bold text-success fs-5">Rp {{ number_format($sale->total, 0, ',', '.') }}</dd>
                            <dt class="col-6 text-muted fw-normal text-end">Dibayar</dt>
                            <dd class="col-6 text-end">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</dd>
                            <dt class="col-6 text-muted fw-normal text-end">Kembalian</dt>
                            <dd class="col-6 text-end fw-semibold">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>

                @if($sale->isVoided())
                    <hr>
                    <div class="alert alert-danger small mb-0">
                        <div class="fw-bold mb-1"><i class="bi bi-x-octagon"></i> Transaksi Di-Void</div>
                        <div><strong>Alasan:</strong> {{ $sale->void_reason }}</div>
                        <div><strong>Oleh:</strong> {{ $sale->voider?->name ?? '—' }} pada {{ $sale->voided_at?->format('d M Y H:i') }}</div>
                        <div class="mt-1 text-muted">Stok semua produk telah dikembalikan.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted mb-3"><i class="bi bi-sliders"></i> Aksi</h6>

                @if($sale->isVoided())
                    <div class="alert alert-secondary mb-0 small">
                        <i class="bi bi-info-circle"></i> Transaksi ini sudah di-void, tidak ada aksi lagi yang tersedia.
                    </div>
                @elseif($lockingOpname)
                    <button type="button" class="btn btn-outline-secondary w-100" disabled>
                        <i class="bi bi-lock"></i> Void Transaksi
                    </button>
                    <div class="alert alert-warning mb-0 small mt-2">
                        <i class="bi bi-lock-fill"></i> Tidak bisa di-void karena sudah ada
                        <strong>Stock Opname {{ $lockingOpname->code }}</strong>
                        ({{ $lockingOpname->opname_date->format('d M Y') }}) setelah tanggal transaksi ini.
                    </div>
                @else
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#voidModal">
                        <i class="bi bi-x-octagon"></i> Void Transaksi
                    </button>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i> Void akan mengembalikan stok semua produk. Hanya untuk transaksi yang keliru.
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>

@if(! $sale->isVoided() && ! $lockingOpname)
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('sales.void', $sale) }}" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-octagon text-danger"></i> Void Transaksi {{ $sale->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Void akan membatalkan transaksi ini dan mengembalikan stok semua produk yang terjual. Tindakan ini tidak bisa dibatalkan.</p>

                <div class="mb-3">
                    <label class="form-label">Alasan Void <span class="text-danger">*</span></label>
                    <textarea name="void_reason" rows="3" class="form-control" required minlength="5"
                              placeholder="Contoh: salah input qty, pelanggan batal..."></textarea>
                    <small class="text-muted">Min. 5 karakter. Alasan akan tercatat permanen.</small>
                </div>

                <div class="mb-2">
                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Password login Anda">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-x-octagon"></i> Void Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<style>
@media print {
    .app-sidebar, .topbar, .alert, .col-lg-4, .btn { display: none !important; }
    .app-main { margin-left: 0 !important; }
    .col-lg-8 { width: 100% !important; max-width: none !important; flex: 0 0 100% !important; }
    .card { border: 0 !important; box-shadow: none !important; }
}
</style>
@endpush
@endsection
