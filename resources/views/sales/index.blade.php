@extends('layouts.app')
@section('title', 'Riwayat Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-cart-check"></i> Riwayat Penjualan</h5>
        <small class="text-muted">Semua transaksi penjualan yang pernah dibuat.</small>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-sm btn-dark">
        <i class="bi bi-plus-lg"></i> Transaksi Baru
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-info bg-opacity-10 text-info mb-2"><i class="bi bi-receipt"></i></div>
                <h6 class="text-muted small mb-1">Transaksi Hari Ini</h6>
                <h4 class="fw-bold mb-0">{{ $summary['today_count'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-success bg-opacity-10 text-success mb-2"><i class="bi bi-cash-coin"></i></div>
                <h6 class="text-muted small mb-1">Omzet Hari Ini</h6>
                <h4 class="fw-bold mb-0">Rp {{ number_format($summary['today_revenue'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-calendar-month"></i></div>
                <h6 class="text-muted small mb-1">Omzet Bulan Ini</h6>
                <h4 class="fw-bold mb-0">Rp {{ number_format($summary['month_revenue'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger mb-2"><i class="bi bi-x-octagon"></i></div>
                <h6 class="text-muted small mb-1">Total Voided</h6>
                <h4 class="fw-bold mb-0">{{ $summary['voided_count'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm"
                       placeholder="Cari kode / pelanggan...">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">— Semua Status —</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                    <option value="voided" @selected($status === 'voided')>Voided</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
            @if($q || $status || $from || $to)
                <div class="col-md-1">
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th class="text-center">Item</th>
                        <th class="text-end">Total</th>
                        <th>Bayar</th>
                        <th class="text-center">Status</th>
                        <th width="80" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr class="{{ $tx->isVoided() ? 'table-secondary' : '' }}">
                            <td><strong>{{ $tx->code }}</strong></td>
                            <td><small>{{ $tx->transaction_date->format('d M Y H:i') }}</small></td>
                            <td>{{ $tx->customer_name ?: '—' }}</td>
                            <td class="text-center">{{ $tx->totalItems() }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                            <td><small>{{ $tx->paymentMethod->name }}</small></td>
                            <td class="text-center">
                                @if($tx->isVoided())
                                    <span class="badge badge-soft-danger">Voided</span>
                                @else
                                    <span class="badge badge-soft-success">Confirmed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $tx) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
