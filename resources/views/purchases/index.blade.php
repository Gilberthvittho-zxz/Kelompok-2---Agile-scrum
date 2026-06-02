@extends('layouts.app')
@section('title', 'Riwayat Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-box-arrow-in-down"></i> Riwayat Pembelian</h5>
        <small class="text-muted">Catatan pembelian sparepart dari supplier.</small>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-dark">
        <i class="bi bi-plus-lg"></i> Pembelian Baru
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-info bg-opacity-10 text-info mb-2"><i class="bi bi-receipt"></i></div>
                <h6 class="text-muted small mb-1">Pembelian Hari Ini</h6>
                <h4 class="fw-bold mb-0">{{ $summary['today_count'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-calendar-month"></i></div>
                <h6 class="text-muted small mb-1">Belanja Bulan Ini</h6>
                <h4 class="fw-bold mb-0">Rp {{ number_format($summary['month_total'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-2"><i class="bi bi-clock"></i></div>
                <h6 class="text-muted small mb-1">Menunggu Tiba</h6>
                <h4 class="fw-bold mb-0">{{ $summary['pending_count'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger mb-2"><i class="bi bi-x-octagon"></i></div>
                <h6 class="text-muted small mb-1">Voided</h6>
                <h4 class="fw-bold mb-0">{{ $summary['voided_count'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari kode / invoice...">
            </div>
            <div class="col-md-3">
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">— Semua Supplier —</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" @selected($supplierId == $sup->id)>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">— Status —</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                    <option value="voided" @selected($status === 'voided')>Voided</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tgl. Beli</th>
                        <th>Tgl. Tiba</th>
                        <th>Supplier</th>
                        <th>Invoice</th>
                        <th class="text-center">Item</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th width="80" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $p)
                        <tr class="{{ $p->isVoided() ? 'table-secondary' : ($p->isPending() ? 'table-warning' : '') }}">
                            <td><strong>{{ $p->code }}</strong></td>
                            <td><small>{{ $p->purchase_date->format('d M Y') }}</small></td>
                            <td>
                                <small>{{ $p->arrival_date?->format('d M Y') ?? '—' }}</small>
                                @if($p->isPending() && $p->arrival_date)
                                    @if($p->arrival_date->isPast())
                                        <i class="bi bi-exclamation-triangle text-danger" title="Sudah lewat tanggal tiba"></i>
                                    @elseif($p->arrival_date->isToday())
                                        <i class="bi bi-clock-history text-warning" title="Tiba hari ini"></i>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $p->supplier->name }}</td>
                            <td><small>{{ $p->invoice_number ?: '—' }}</small></td>
                            <td class="text-center">{{ $p->totalItems() }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($p->isVoided())
                                    <span class="badge badge-soft-danger">Voided</span>
                                @elseif($p->isPending())
                                    <span class="badge badge-soft-warning">Pending</span>
                                @else
                                    <span class="badge badge-soft-success">Confirmed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('purchases.show', $p) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada pembelian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $purchases->links() }}</div>
    </div>
</div>
@endsection
