@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h3 class="mb-0">Dashboard</h3>
    <small class="text-muted">Ringkasan inventory MOTOKU</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-tags"></i></div>
                <h6 class="text-muted small mb-1">Total Kategori</h6>
                <h3 class="fw-bold mb-0">{{ $stats['categories'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-success bg-opacity-10 text-success mb-2"><i class="bi bi-truck"></i></div>
                <h6 class="text-muted small mb-1">Total Supplier</h6>
                <h3 class="fw-bold mb-0">{{ $stats['suppliers'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-info bg-opacity-10 text-info mb-2"><i class="bi bi-box-seam"></i></div>
                <h6 class="text-muted small mb-1">Total Produk</h6>
                <h3 class="fw-bold mb-0">{{ $stats['products'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-2"><i class="bi bi-exclamation-triangle"></i></div>
                <h6 class="text-muted small mb-1">Produk Stok Menipis</h6>
                <h3 class="fw-bold mb-0">{{ $stats['low_stock'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="text-muted">Selamat datang, <strong>{{ Auth::user()->name }}</strong> 👋</h6>
        <p class="text-muted small mb-0">
            Sistem MOTOKU membantu Anda mengelola sparepart motor — dari data master hingga laporan stok real-time.
            Sprint berikutnya akan menghadirkan transaksi penjualan, pembelian, dan laporan lengkap.
        </p>
    </div>
</div>
@endsection
