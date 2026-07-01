@extends('layouts.app')
@section('title', 'Stock Opname')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Stock Opname</h5>
        <small class="text-muted">Riwayat penghitungan stok fisik dan penyesuaiannya.</small>
    </div>
    <a href="{{ route('stock-opnames.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg"></i> Opname Baru
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
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
                        <th class="text-center">Produk Disesuaikan</th>
                        <th>Catatan Umum</th>
                        <th>Oleh</th>
                        <th width="80" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames as $opname)
                        <tr>
                            <td><strong>{{ $opname->code }}</strong></td>
                            <td><small>{{ $opname->opname_date->format('d M Y H:i') }}</small></td>
                            <td class="text-center">{{ $opname->details->count() }}</td>
                            <td><small>{{ \Illuminate\Support\Str::limit($opname->note, 50) ?: '—' }}</small></td>
                            <td><small>{{ $opname->creator?->name ?? '—' }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('stock-opnames.show', $opname) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada opname.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $opnames->links() }}</div>
    </div>
</div>
@endsection
