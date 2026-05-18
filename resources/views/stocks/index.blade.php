@extends('layouts.app')
@section('title', 'Stok Real-time')

@section('content')
<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-clipboard-data"></i> Sisa Stok Real-time</h3>
    <small class="text-muted">Pantau stok semua produk; baris berwarna menunjukkan stok di bawah batas minimum.</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-box-seam"></i></div>
                <div>
                    <small class="text-muted d-block">Total Item</small>
                    <h4 class="fw-bold mb-0">{{ $summary['total_items'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <small class="text-muted d-block">Stok Menipis</small>
                    <h4 class="fw-bold mb-0">{{ $summary['low_stock'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-octagon"></i></div>
                <div>
                    <small class="text-muted d-block">Stok Habis</small>
                    <h4 class="fw-bold mb-0">{{ $summary['out_of_stock'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Filter Kategori</label>
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">— Semua Kategori —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="low_only" value="1" id="low_only" class="form-check-input"
                           {{ $lowOnly ? 'checked' : '' }} onchange="this.form.submit()">
                    <label for="low_only" class="form-check-label">Hanya tampilkan stok menipis</label>
                </div>
            </div>
            @if($categoryId || $lowOnly)
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary w-100">Reset Filter</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th width="70">Gambar</th>
                        <th>Kode / Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Stok Min.</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $i => $p)
                        @php
                            $isOut = $p->stock <= 0;
                            $isLow = !$isOut && $p->isLowStock();
                            $rowClass = $isOut ? 'table-danger' : ($isLow ? 'table-warning' : '');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>{{ $products->firstItem() + $i }}</td>
                            <td><img src="{{ $p->image_url }}" alt="" class="product-thumb"></td>
                            <td>
                                <div class="small text-muted">{{ $p->code ?: '—' }}</div>
                                <strong>{{ $p->name }}</strong>
                            </td>
                            <td>{{ $p->category?->name }}</td>
                            <td class="text-center fw-bold fs-5">{{ $p->stock }}</td>
                            <td class="text-center text-muted">{{ $p->min_stock }}</td>
                            <td class="text-center">
                                @if($isOut)
                                    <span class="badge badge-soft-danger"><i class="bi bi-x-octagon"></i> Habis</span>
                                @elseif($isLow)
                                    <span class="badge badge-soft-warning"><i class="bi bi-exclamation-triangle"></i> Stok Menipis</span>
                                @else
                                    <span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Aman</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada produk yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $products->links() }}</div>
    </div>
</div>
@endsection
