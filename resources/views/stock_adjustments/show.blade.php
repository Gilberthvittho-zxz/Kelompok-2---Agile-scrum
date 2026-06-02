@extends('layouts.app')
@section('title', 'Detail Adjustment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Detail Adjustment</h5>
        <small class="text-muted">{{ $adjustment->code }}</small>
    </div>
    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $adjustment->code }}</h4>
                <div class="small text-muted">{{ $adjustment->adjustment_date->format('l, d M Y H:i') }}</div>
                <span class="badge bg-secondary mt-1">{{ $adjustment->reasonLabel() }}</span>
            </div>
            <div class="text-end small text-muted">
                <div>Oleh: <strong>{{ $adjustment->creator?->name ?? '—' }}</strong></div>
                <div>{{ $adjustment->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>

        @if($adjustment->note)
            <div class="alert alert-light small">
                <span class="text-muted">Catatan:</span> {{ $adjustment->note }}
            </div>
        @endif

        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th class="text-center">Stok Sebelum</th>
                    <th class="text-center">Stok Sesudah</th>
                    <th class="text-center">Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustment->details as $i => $d)
                    @php
                        $diffClass = $d->qty_diff > 0 ? 'text-success' : 'text-danger';
                        $sign = $d->qty_diff > 0 ? '+' : '';
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="small text-muted">{{ $d->product_code_snapshot ?: '—' }}</div>
                            <strong>{{ $d->product_name_snapshot }}</strong>
                        </td>
                        <td class="text-center">{{ $d->qty_before }}</td>
                        <td class="text-center fw-semibold">{{ $d->qty_after }}</td>
                        <td class="text-center fw-bold {{ $diffClass }}">{{ $sign }}{{ $d->qty_diff }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
