@extends('layouts.app')
@section('title', 'Stock Adjustment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Stock Adjustment</h5>
        <small class="text-muted">Koreksi stok manual untuk kondisi di luar transaksi normal.</small>
    </div>
    <a href="{{ route('stock-adjustments.create') }}" class="btn btn-sm btn-dark">
        <i class="bi bi-plus-lg"></i> Adjustment Baru
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="reason" class="form-select form-select-sm">
                    <option value="">— Semua Alasan —</option>
                    @foreach($reasons as $k => $label)
                        <option value="{{ $k }}" @selected($reason === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary w-100">Filter</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Alasan</th>
                        <th class="text-center">Item Diubah</th>
                        <th>Catatan</th>
                        <th>Oleh</th>
                        <th width="80" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        <tr>
                            <td><strong>{{ $adj->code }}</strong></td>
                            <td><small>{{ $adj->adjustment_date->format('d M Y H:i') }}</small></td>
                            <td><span class="badge bg-light text-dark">{{ $adj->reasonLabel() }}</span></td>
                            <td class="text-center">{{ $adj->details->count() }}</td>
                            <td><small>{{ \Illuminate\Support\Str::limit($adj->note, 50) ?: '—' }}</small></td>
                            <td><small>{{ $adj->creator?->name ?? '—' }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('stock-adjustments.show', $adj) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada adjustment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $adjustments->links() }}</div>
    </div>
</div>
@endsection
