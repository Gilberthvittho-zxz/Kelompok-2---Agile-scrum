@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
<div class="mb-4">
    <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</h5>
    <small class="text-muted">Pilih jenis laporan yang ingin dilihat.</small>
</div>

<div class="row g-3">
    @php
        $cards = [
            [
                'route' => 'reports.sales',
                'title' => 'Laporan Penjualan',
                'desc'  => 'Rekap transaksi penjualan per periode — omzet, item terjual, produk terlaris, dan metode pembayaran.',
                'icon'  => 'bi-cart-check',
                'color' => '#2563eb',
            ],
            [
                'route' => 'reports.purchases',
                'title' => 'Laporan Pembelian',
                'desc'  => 'Rekap pembelian dari supplier per periode — total belanja, produk dibeli, dan rekap per supplier.',
                'icon'  => 'bi-box-arrow-in-down',
                'color' => '#0f766e',
            ],
            [
                'route' => 'reports.waste',
                'title' => 'Laporan Waste',
                'desc'  => 'Rekap barang terbuang (rusak, expired, hilang) per rentang tanggal beserta produk paling sering waste.',
                'icon'  => 'bi-trash3',
                'color' => '#dc2626',
            ],
        ];
    @endphp

    @foreach($cards as $card)
        <div class="col-md-4 col-sm-6">
            <a href="{{ route($card['route']) }}" class="text-decoration-none">
                <div class="card report-card shadow-sm border-0 h-100" style="border-top: 4px solid {{ $card['color'] }} !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <div class="report-icon" style="background: {{ $card['color'] }}1a; color: {{ $card['color'] }};">
                                <i class="bi {{ $card['icon'] }}"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0 mt-1">{{ $card['title'] }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ $card['desc'] }}</p>
                        <span class="fw-semibold small" style="color: {{ $card['color'] }};">
                            Buka Laporan <i class="bi bi-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@push('scripts')
<style>
    .report-card { transition: transform .15s ease, box-shadow .15s ease; }
    .report-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.1) !important; }
    .report-icon {
        width: 44px; height: 44px; border-radius: .6rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
    }
</style>
@endpush
@endsection
