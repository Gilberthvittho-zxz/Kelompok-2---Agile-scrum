@extends('layouts.app')
@section('title', 'Detail Opname')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Detail Stock Opname</h5>
        <small class="text-muted">{{ $opname->code }}</small>
    </div>
    <div class="d-flex gap-2">
        @if($laterActivity)
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Terkunci">
                <i class="bi bi-lock"></i> Hapus
            </button>
        @else
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteOpnameModal">
                <i class="bi bi-trash"></i> Hapus
            </button>
        @endif
        <a href="{{ route('stock-opnames.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@if($laterActivity)
    <div class="alert alert-warning small">
        <i class="bi bi-lock-fill"></i> Opname ini tidak bisa dihapus karena sudah ada <strong>{{ $laterActivity }}</strong> setelahnya.
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $opname->code }}</h4>
                <div class="small text-muted">{{ $opname->opname_date->format('l, d M Y H:i') }}</div>
            </div>
            <div class="text-end small text-muted">
                <div>Oleh: <strong>{{ $opname->creator?->name ?? '—' }}</strong></div>
                <div>{{ $opname->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>

        @if($opname->note)
            <div class="alert alert-light small">
                <span class="text-muted">Catatan umum:</span> {{ $opname->note }}
            </div>
        @endif

        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th class="text-center">Stok Sistem</th>
                    <th class="text-center">Stok Fisik</th>
                    <th class="text-center">Selisih</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opname->details as $i => $d)
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
                        <td class="text-center">{{ $d->qty_system }}</td>
                        <td class="text-center fw-semibold">{{ $d->qty_physical }}</td>
                        <td class="text-center fw-bold {{ $diffClass }}">{{ $sign }}{{ $d->qty_diff }}</td>
                        <td><small class="text-muted">{{ $d->note ?: '—' }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-success py-4">
                            <i class="bi bi-check-circle"></i> Semua stok fisik cocok dengan sistem — tidak ada selisih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@unless($laterActivity)
<div class="modal fade" id="deleteOpnameModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('stock-opnames.destroy', $opname) }}" class="modal-content">
            @csrf @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Hapus Opname {{ $opname->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Menghapus opname ini akan <strong>mengembalikan stok</strong> produk ke kondisi sebelum opname. Tindakan ini tidak bisa dibatalkan.</p>
                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" name="confirm_password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus Opname</button>
            </div>
        </form>
    </div>
</div>
@endunless
@endsection
