@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h3 class="mb-0">Dashboard</h3>
        <small class="text-muted">Ringkasan operasional MOTOKU hari ini, {{ now()->translatedFormat('l, d F Y') }}</small>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Buat Transaksi Penjualan
    </a>
</div>

{{-- ===== Kartu Ringkasan Hari Ini ===== --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-success bg-opacity-10 text-success mb-2"><i class="bi bi-cash-coin"></i></div>
                <h6 class="text-muted small mb-1">Pendapatan Hari Ini</h6>
                <h3 class="fw-bold mb-0">Rp {{ number_format($todaySummary['revenue'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-info bg-opacity-10 text-info mb-2"><i class="bi bi-receipt"></i></div>
                <h6 class="text-muted small mb-1">Total Transaksi Hari Ini</h6>
                <h3 class="fw-bold mb-0">{{ $todaySummary['transactions'] }} <small class="fs-6 text-muted fw-normal">transaksi</small></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-box-seam"></i></div>
                <h6 class="text-muted small mb-1">Produk Terjual Hari Ini</h6>
                <h3 class="fw-bold mb-0">{{ $todaySummary['items_sold'] }} <small class="fs-6 text-muted fw-normal">pcs</small></h3>
            </div>
        </div>
    </div>
</div>

{{-- ===== Grafik Penjualan + Top 5 Produk ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-graph-up-arrow"></i> Grafik Pendapatan Penjualan (7 Hari Terakhir)</h6>
                <div style="position: relative; height: 260px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-trophy"></i> Top 5 Produk Terlaris Hari Ini</h6>
                @forelse($topProducts as $i => $p)
                    <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rank-badge">{{ $i + 1 }}</span>
                            <div>
                                <div class="fw-semibold small">{{ $p->product_name_snapshot }}</div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    Rp {{ number_format($p->total_revenue, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-soft-success">{{ $p->total_qty }} pcs</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-4 mb-0">Belum ada penjualan hari ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ===== Low Stock Alert ===== --}}
<div class="card shadow-sm border-0 mb-3 overflow-hidden">
    <div class="bg-danger text-white px-3 py-2 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Produk Stok Menipis (Perlu Restock!)</strong>
        @if($lowStockProducts->count() > 0)
            <span class="badge bg-white text-danger ms-1">{{ $stats['low_stock'] }}</span>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th width="50">No.</th>
                    <th>Produk</th>
                    <th class="text-center">Sisa Stok</th>
                    <th>Supplier</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockProducts as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $p->name }}</div>
                            <small class="text-muted">{{ $p->code }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $p->stock == 0 ? 'badge-soft-danger' : 'badge-soft-warning' }}">
                                {{ $p->stock }} pcs
                            </span>
                        </td>
                        <td>{{ $p->supplier->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-cart-plus"></i> Beli
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-check-circle text-success"></i> Semua stok produk aman, tidak ada yang perlu di-restock.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stats['low_stock'] > $lowStockProducts->count())
        <div class="px-3 py-2 text-center small text-muted no-print">
            Menampilkan {{ $lowStockProducts->count() }} dari {{ $stats['low_stock'] }} produk stok menipis.
            <a href="{{ route('stocks.index', ['low_only' => 1]) }}">Lihat semua &rarr;</a>
        </div>
    @endif
</div>

{{-- ===== Ringkasan Master Data ===== --}}
<div class="row g-3">
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-tags"></i></div>
                <div>
                    <h6 class="text-muted small mb-1">Total Kategori</h6>
                    <h5 class="fw-bold mb-0">{{ $stats['categories'] }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-truck"></i></div>
                <div>
                    <h6 class="text-muted small mb-1">Total Supplier</h6>
                    <h5 class="fw-bold mb-0">{{ $stats['suppliers'] }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-box-seam"></i></div>
                <div>
                    <h6 class="text-muted small mb-1">Total Produk</h6>
                    <h5 class="fw-bold mb-0">{{ $stats['products'] }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<style>
    .rank-badge {
        width: 24px; height: 24px;
        border-radius: 50%;
        background: #1f2937;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
<script>
    (function () {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        const labels = @json($salesChart->pluck('label'));
        const data = @json($salesChart->pluck('revenue'));

        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(59,130,246,0.25)');
        gradient.addColorStop(1, 'rgba(59,130,246,0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: data,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f3f7' },
                        ticks: {
                            callback: (val) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(val)
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    })();
</script>
@endpush
@endsection