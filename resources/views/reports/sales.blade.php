@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Penjualan</h5>
        <small class="text-muted">Rekap transaksi penjualan berdasarkan rentang tanggal.</small>
    </div>
    @if($transactions->count() > 0)
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak
        </button>
    @endif
</div>

{{-- ===== Form Filter Tanggal Awal - Akhir ===== --}}
<div class="card shadow-sm border-0 mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small text-muted mb-1">Tanggal Awal</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small text-muted mb-1">Tanggal Akhir</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2 col-sm-6">
                <button class="btn btn-sm btn-dark w-100"><i class="bi bi-funnel"></i> Tampilkan</button>
            </div>
            <div class="col-md-2 col-sm-6">
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Header khusus tampilan cetak --}}
<div class="text-center mb-3 d-none d-print-block">
    <h5 class="mb-0">LAPORAN PENJUALAN</h5>
    <small>
        Periode {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }}
        &ndash; {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}
    </small>
</div>

{{-- ===== Kartu Ringkasan ===== --}}
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-info bg-opacity-10 text-info mb-2"><i class="bi bi-receipt"></i></div>
                <h6 class="text-muted small mb-1">Total Transaksi</h6>
                <h4 class="fw-bold mb-0">{{ $summary['total_transaksi'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-success bg-opacity-10 text-success mb-2"><i class="bi bi-cash-coin"></i></div>
                <h6 class="text-muted small mb-1">Total Omzet</h6>
                <h4 class="fw-bold mb-0">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-box-seam"></i></div>
                <h6 class="text-muted small mb-1">Item Terjual</h6>
                <h4 class="fw-bold mb-0">{{ $summary['total_item'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-2"><i class="bi bi-graph-up"></i></div>
                <h6 class="text-muted small mb-1">Rata-rata / Transaksi</h6>
                <h4 class="fw-bold mb-0">Rp {{ number_format($summary['rata_rata'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- ===== Detail Transaksi (klik baris untuk lihat item) ===== --}}
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <h6 class="fw-semibold mb-3"><i class="bi bi-list-check"></i> Detail Transaksi</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="30" class="no-print"></th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th class="text-center">Item</th>
                        <th>Metode Bayar</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr class="report-row" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#detail-{{ $tx->id }}">
                            <td class="no-print text-center"><i class="bi bi-chevron-down text-muted"></i></td>
                            <td><strong>{{ $tx->code }}</strong></td>
                            <td><small>{{ $tx->transaction_date->format('d M Y H:i') }}</small></td>
                            <td>{{ $tx->customer_name ?: '—' }}</td>
                            <td class="text-center">{{ $tx->totalItems() }}</td>
                            <td><small>{{ $tx->paymentMethod->name }}</small></td>
                            <td class="text-end fw-semibold">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="collapse" id="detail-{{ $tx->id }}">
                            <td></td>
                            <td colspan="6" class="bg-light">
                                <table class="table table-sm mb-2">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tx->details as $d)
                                            <tr>
                                                <td>{{ $d->product_name_snapshot }} <small class="text-muted">({{ $d->product_code_snapshot }})</small></td>
                                                <td class="text-center">{{ $d->qty }}</td>
                                                <td class="text-end">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Diskon: Rp {{ number_format($tx->discount, 0, ',', '.') }}</span>
                                    <a href="{{ route('sales.show', $tx) }}" class="no-print">
                                        <i class="bi bi-box-arrow-up-right"></i> Lihat detail lengkap
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi pada rentang tanggal ini.</td></tr>
                    @endforelse
                </tbody>
                @if($transactions->count() > 0)
                <tfoot>
                    <tr class="table-light">
                        <th colspan="6" class="text-end">TOTAL</th>
                        <th class="text-end">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- ===== Rekap Produk Terjual ===== --}}
<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="fw-semibold mb-3"><i class="bi bi-trophy"></i> Rekap Produk Terjual</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Produk</th>
                        <th>Kode</th>
                        <th class="text-center">Total Qty</th>
                        <th class="text-end">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productRecap as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $p->product_name_snapshot }}</td>
                            <td><small class="text-muted">{{ $p->product_code_snapshot }}</small></td>
                            <td class="text-center">{{ $p->total_qty }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($p->total_subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data produk terjual.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .report-row .bi-chevron-down { transition: transform .2s ease; }
    .report-row[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }

    @media print {
        .no-print, .app-sidebar, .topbar, .sidebar-backdrop { display: none !important; }
        .app-main { margin-left: 0 !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
</style>
@endpush
@endsection