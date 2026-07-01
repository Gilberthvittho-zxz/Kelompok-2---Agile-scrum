@extends('layouts.app')
@section('title', 'Detail Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Detail Pembelian</h5>
        <small class="text-muted">{{ $purchase->code }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-printer"></i> Cetak
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="fw-bold mb-1">{{ $purchase->code }}</h4>
                        <div class="small text-muted">
                            Tgl. Pembelian: <strong>{{ $purchase->purchase_date->format('d M Y') }}</strong>
                            &nbsp;·&nbsp;
                            Tgl. Tiba: <strong>{{ $purchase->arrival_date?->format('d M Y') ?? '—' }}</strong>
                        </div>
                        @if($purchase->isVoided())
                            <span class="badge badge-soft-danger mt-1"><i class="bi bi-x-octagon"></i> VOIDED</span>
                        @elseif($purchase->isPending())
                            <span class="badge badge-soft-warning mt-1"><i class="bi bi-clock"></i> PENDING — barang belum tiba</span>
                        @else
                            <span class="badge badge-soft-success mt-1"><i class="bi bi-check-circle"></i> CONFIRMED — stok sudah masuk</span>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Supplier</div>
                        <div class="fw-semibold">{{ $purchase->supplier->name }}</div>
                        @if($purchase->invoice_number)
                            <div class="small text-muted mt-1">No. Invoice</div>
                            <div>{{ $purchase->invoice_number }}</div>
                        @endif
                    </div>
                </div>

                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga Beli/Unit</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->details as $i => $d)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <div class="small text-muted">{{ $d->product_code_snapshot ?: '—' }}</div>
                                    <strong>{{ $d->product_name_snapshot }}</strong>
                                </td>
                                <td class="text-center">{{ $d->qty }}</td>
                                <td class="text-end">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">TOTAL</th>
                            <th class="text-end text-success fs-5">Rp {{ number_format($purchase->total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>

                @if($purchase->note)
                    <hr>
                    <div class="small">
                        <span class="text-muted">Catatan:</span> {{ $purchase->note }}
                    </div>
                @endif

                <div class="d-flex justify-content-between small text-muted mt-3">
                    <span><i class="bi bi-person"></i> Dicatat oleh: {{ $purchase->creator?->name ?? '—' }}</span>
                    <span>{{ $purchase->created_at->format('d M Y H:i') }}</span>
                </div>
                @if($purchase->arrived_at)
                    <div class="small text-success mt-1">
                        <i class="bi bi-check-circle"></i> Kedatangan dikonfirmasi: {{ $purchase->arrived_at->format('d M Y H:i') }}
                        @if($purchase->arrivedBy)
                            oleh {{ $purchase->arrivedBy->name }}
                        @endif
                    </div>
                @endif

                @if($purchase->isVoided())
                    <hr>
                    <div class="alert alert-danger small mb-0">
                        <div class="fw-bold mb-1"><i class="bi bi-x-octagon"></i> Pembelian Di-Void</div>
                        <div><strong>Alasan:</strong> {{ $purchase->void_reason }}</div>
                        <div><strong>Oleh:</strong> {{ $purchase->voider?->name ?? '—' }} pada {{ $purchase->voided_at?->format('d M Y H:i') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted mb-3"><i class="bi bi-sliders"></i> Aksi</h6>

                @if($purchase->isPending())
                    <form method="POST" action="{{ route('purchases.confirm-arrival', $purchase) }}"
                          onsubmit="return confirm('Konfirmasi barang sudah tiba? Stok produk akan langsung bertambah.')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-truck"></i> Konfirmasi Kedatangan
                        </button>
                    </form>
                    <small class="text-muted d-block mb-3">
                        <i class="bi bi-info-circle"></i> Klik saat barang benar-benar sudah tiba di toko. Stok produk akan otomatis bertambah.
                    </small>
                @endif

                @if($purchase->isVoided())
                    <div class="alert alert-secondary mb-0 small">
                        <i class="bi bi-info-circle"></i> Pembelian sudah di-void.
                    </div>
                @elseif($lockingOpname)
                    <button type="button" class="btn btn-outline-secondary w-100" disabled>
                        <i class="bi bi-lock"></i> Void Pembelian
                    </button>
                    <div class="alert alert-warning mb-0 small mt-2">
                        <i class="bi bi-lock-fill"></i> Tidak bisa di-void karena sudah ada
                        <strong>Stock Opname {{ $lockingOpname->code }}</strong>
                        ({{ $lockingOpname->opname_date->format('d M Y') }}) setelah tanggal pembelian ini.
                    </div>
                @else
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#voidModal">
                        <i class="bi bi-x-octagon"></i> Void Pembelian
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@if(! $purchase->isVoided() && ! $lockingOpname)
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('purchases.void', $purchase) }}" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-octagon text-danger"></i> Void Pembelian {{ $purchase->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Void akan mengurangi stok semua produk sesuai jumlah dibeli. Pastikan stok masih cukup (belum terjual).</p>
                <div class="mb-3">
                    <label class="form-label">Alasan Void <span class="text-danger">*</span></label>
                    <textarea name="void_reason" rows="3" class="form-control" required minlength="5" placeholder="Contoh: salah input, barang dikembalikan..."></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-x-octagon"></i> Void Pembelian</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
