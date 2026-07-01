@extends('layouts.app')
@section('title', 'Laporan Waste')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h5 class="mb-0"><i class="bi bi-trash3"></i> Laporan Waste</h5>
            <small class="text-muted">Rekap barang terbuang (rusak, expired, hilang) berdasarkan rentang tanggal.</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.waste.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
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
                <a href="{{ route('reports.waste') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="text-center mb-3 d-none d-print-block">
    <h5 class="mb-0">LAPORAN WASTE</h5>
    <small>Periode {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}</small>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-danger bg-opacity-10 text-danger mb-2"><i class="bi bi-trash3"></i></div>
        <h6 class="text-muted small mb-1">Total Item Terbuang</h6>
        <h4 class="fw-bold mb-0">{{ $wasteSummary['total_item'] }}</h4>
    </div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-info bg-opacity-10 text-info mb-2"><i class="bi bi-receipt"></i></div>
        <h6 class="text-muted small mb-1">Catatan Waste</h6>
        <h4 class="fw-bold mb-0">{{ $wasteSummary['total_catatan'] }}</h4>
    </div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-box-seam"></i></div>
        <h6 class="text-muted small mb-1">Produk Terkena</h6>
        <h4 class="fw-bold mb-0">{{ $wasteSummary['produk_kena'] }}</h4>
    </div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card shadow-sm"><div class="card-body">
        <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-2"><i class="bi bi-tags"></i></div>
        <h6 class="text-muted small mb-1">Jenis Alasan</h6>
        <h4 class="fw-bold mb-0">{{ $wasteSummary['jenis_alasan'] }}</h4>
    </div></div></div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="mb-4 no-print" style="max-width:340px">
            <label class="form-label small text-muted mb-1">Tampilkan</label>
            <select class="form-select submenu-select" data-content="#w-content">
                <option value="#w-detail">📋 Detail Waste</option>
                <option value="#w-produk">⚠️ Produk Paling Sering Waste</option>
                <option value="#w-alasan">🏷️ Per Alasan</option>
            </select>
        </div>

        <div class="tab-content" id="w-content">
            {{-- Detail Waste --}}
            <div class="tab-pane fade show active" id="w-detail" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr>
                            <th width="30" class="no-print"></th>
                            <th>Kode</th><th>Tanggal</th><th class="text-center">Produk</th><th>Catatan</th><th>Oleh</th>
                        </tr></thead>
                        <tbody>
                            @forelse($adjustments as $adj)
                                <tr class="report-row" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#w-row-{{ $adj->id }}">
                                    <td class="no-print text-center"><i class="bi bi-chevron-down text-muted"></i></td>
                                    <td><strong>{{ $adj->code }}</strong></td>
                                    <td><small>{{ $adj->adjustment_date->format('d M Y H:i') }}</small></td>
                                    <td class="text-center">{{ $adj->details->count() }}</td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($adj->note, 40) ?: '—' }}</small></td>
                                    <td><small>{{ $adj->creator?->name ?? '—' }}</small></td>
                                </tr>
                                <tr class="collapse" id="w-row-{{ $adj->id }}">
                                    <td></td>
                                    <td colspan="5" class="bg-light">
                                        <table class="table table-sm mb-0">
                                            <thead><tr><th>Produk</th><th class="text-center">Stok Sebelum</th><th class="text-center">Stok Sesudah</th><th class="text-center">Terbuang</th><th>Alasan</th><th>Catatan</th></tr></thead>
                                            <tbody>
                                                @foreach($adj->details as $d)
                                                    <tr>
                                                        <td>{{ $d->product_name_snapshot }} <small class="text-muted">({{ $d->product_code_snapshot }})</small></td>
                                                        <td class="text-center">{{ $d->qty_before }}</td>
                                                        <td class="text-center">{{ $d->qty_after }}</td>
                                                        <td class="text-center fw-bold text-danger">{{ $d->qty_diff < 0 ? $d->qty_diff * -1 : 0 }}</td>
                                                        <td><span class="badge {{ $d->reasonBadgeClass() }}">{{ $d->reasonLabel() }}</span></td>
                                                        <td><small class="text-muted">{{ $d->note ?: '—' }}</small></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada catatan waste pada rentang tanggal ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Produk Paling Sering Waste --}}
            <div class="tab-pane fade" id="w-produk" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th width="40">#</th><th>Produk</th><th>Kode</th><th class="text-center">Total Terbuang</th><th class="text-center">Jumlah Kejadian</th></tr></thead>
                        <tbody>
                            @forelse($wasteProductRecap as $i => $p)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $p->product_name_snapshot }}</td>
                                    <td><small class="text-muted">{{ $p->product_code_snapshot }}</small></td>
                                    <td class="text-center fw-semibold text-danger">{{ $p->total_qty }}</td>
                                    <td class="text-center">{{ $p->total_kejadian }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data waste.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Per Alasan --}}
            <div class="tab-pane fade" id="w-alasan" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th width="40">#</th><th>Alasan</th><th class="text-center">Jumlah Kejadian</th><th class="text-center">Total Terbuang</th><th class="text-end">% dari Total</th></tr></thead>
                        <tbody>
                            @forelse($wasteReasonRecap as $i => $r)
                                @php $persen = $wasteSummary['total_item'] > 0 ? ($r->total_qty / $wasteSummary['total_item']) * 100 : 0; @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $reasons[$r->reason] ?? $r->reason }}</td>
                                    <td class="text-center">{{ $r->total_kejadian }}</td>
                                    <td class="text-center fw-semibold text-danger">{{ $r->total_qty }}</td>
                                    <td class="text-end">{{ number_format($persen, 1, ',', '.') }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data alasan.</td></tr>
                            @endforelse
                        </tbody>
                        @if($wasteReasonRecap->count() > 0)
                        <tfoot><tr class="table-light"><th colspan="3" class="text-end">TOTAL</th><th class="text-center">{{ $wasteSummary['total_item'] }}</th><th class="text-end">100%</th></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('reports._report_assets')
@endsection
