@extends('layouts.app')
@section('title', 'Laporan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h5 class="mb-0"><i class="bi bi-box-arrow-in-down"></i> Laporan Pembelian</h5>
            <small class="text-muted">Rekap pembelian dari supplier berdasarkan rentang tanggal.</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.purchases.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
    </div>
</div>

{{-- Filter --}}
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
                <a href="{{ route('reports.purchases') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="text-center mb-3 d-none d-print-block">
    <h5 class="mb-0">LAPORAN PEMBELIAN</h5>
    <small>Periode {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}</small>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-info bg-opacity-10 text-info mb-2"><i class="bi bi-receipt"></i></div>
        <h6 class="text-muted small mb-1">Total Pembelian</h6>
        <h4 class="fw-bold mb-0">{{ $purchaseSummary['total_pembelian'] }}</h4>
    </div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-danger bg-opacity-10 text-danger mb-2"><i class="bi bi-box-arrow-in-down"></i></div>
        <h6 class="text-muted small mb-1">Total Belanja</h6>
        <h4 class="fw-bold mb-0">Rp {{ number_format($purchaseSummary['total_belanja'], 0, ',', '.') }}</h4>
    </div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-box-seam"></i></div>
        <h6 class="text-muted small mb-1">Item Dibeli</h6>
        <h4 class="fw-bold mb-0">{{ $purchaseSummary['total_item'] }}</h4>
    </div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-2"><i class="bi bi-graph-up"></i></div>
        <h6 class="text-muted small mb-1">Rata-rata / Pembelian</h6>
        <h4 class="fw-bold mb-0">Rp {{ number_format($purchaseSummary['rata_rata'], 0, ',', '.') }}</h4>
    </div></div></div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="mb-4 no-print" style="max-width:320px">
            <label class="form-label small text-muted mb-1">Tampilkan</label>
            <select class="form-select submenu-select" data-content="#p-content">
                <option value="#p-detail">📋 Detail Pembelian</option>
                <option value="#p-produk">📦 Rekap Produk Dibeli</option>
                <option value="#p-supplier">🚚 Rekap per Supplier</option>
            </select>
        </div>

        <div class="tab-content" id="p-content">
            {{-- Detail Pembelian --}}
            <div class="tab-pane fade show active" id="p-detail" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr>
                            <th width="30" class="no-print"></th>
                            <th>Kode</th><th>Tgl. Beli</th><th>Supplier</th><th>Invoice</th>
                            <th class="text-center">Item</th><th class="text-center">Status</th><th class="text-end">Total</th>
                        </tr></thead>
                        <tbody>
                            @forelse($purchases as $p)
                                <tr class="report-row" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#p-row-{{ $p->id }}">
                                    <td class="no-print text-center"><i class="bi bi-chevron-down text-muted"></i></td>
                                    <td><strong>{{ $p->code }}</strong></td>
                                    <td><small>{{ $p->purchase_date->format('d M Y') }}</small></td>
                                    <td>{{ $p->supplier->name }}</td>
                                    <td><small>{{ $p->invoice_number ?: '—' }}</small></td>
                                    <td class="text-center">{{ $p->totalItems() }}</td>
                                    <td class="text-center">
                                        @if($p->isPending())
                                            <span class="badge badge-soft-warning">Pending</span>
                                        @else
                                            <span class="badge badge-soft-success">Confirmed</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="collapse" id="p-row-{{ $p->id }}">
                                    <td></td>
                                    <td colspan="7" class="bg-light">
                                        <table class="table table-sm mb-2">
                                            <thead><tr><th>Produk</th><th class="text-center">Qty</th><th class="text-end">Harga Beli</th><th class="text-end">Subtotal</th></tr></thead>
                                            <tbody>
                                                @foreach($p->details as $d)
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
                                            <span>Tgl. Tiba: {{ $p->arrival_date?->format('d M Y') ?? '—' }}</span>
                                            <a href="{{ route('purchases.show', $p) }}" class="no-print"><i class="bi bi-box-arrow-up-right"></i> Lihat detail lengkap</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pembelian pada rentang tanggal ini.</td></tr>
                            @endforelse
                        </tbody>
                        @if($purchases->count() > 0)
                        <tfoot><tr class="table-light"><th colspan="7" class="text-end">TOTAL</th><th class="text-end">Rp {{ number_format($purchaseSummary['total_belanja'], 0, ',', '.') }}</th></tr></tfoot>
                        @endif
                    </table>
                </div>
                <small class="text-muted no-print"><i class="bi bi-info-circle"></i> Pembelian dengan status <em>voided</em> tidak dihitung dalam laporan ini.</small>
            </div>

            {{-- Rekap Produk Dibeli --}}
            <div class="tab-pane fade" id="p-produk" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th width="40">#</th><th>Produk</th><th>Kode</th><th class="text-center">Total Qty</th><th class="text-end">Total Pembelian</th></tr></thead>
                        <tbody>
                            @forelse($purchaseProductRecap as $i => $p)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $p->product_name_snapshot }}</td>
                                    <td><small class="text-muted">{{ $p->product_code_snapshot }}</small></td>
                                    <td class="text-center">{{ $p->total_qty }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($p->total_subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data produk dibeli.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Rekap per Supplier --}}
            <div class="tab-pane fade" id="p-supplier" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th width="40">#</th><th>Supplier</th><th class="text-center">Jumlah Pembelian</th><th class="text-end">Total Belanja</th></tr></thead>
                        <tbody>
                            @forelse($supplierRecap as $i => $s)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $s->supplier->name }}</td>
                                    <td class="text-center">{{ $s->total_pembelian }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($s->total_belanja, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data supplier.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('reports._report_assets')
@endsection
