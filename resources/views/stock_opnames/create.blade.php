@extends('layouts.app')
@section('title', 'Stock Opname Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Stock Opname Baru</h5>
        <small class="text-muted">Hitung stok fisik di gudang. Sistem otomatis menyesuaikan stok sesuai hitungan fisik.</small>
    </div>
    <a href="{{ route('stock-opnames.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        <i class="bi bi-exclamation-triangle"></i>
        @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('stock-opnames.store') }}" id="opnameForm">
    @csrf

    {{-- HEADER --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Tanggal Opname <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="opname_date" value="{{ old('opname_date', now()->format('Y-m-d\TH:i')) }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label small text-muted">Catatan Umum (opsional)</label>
                    <input type="text" name="note" value="{{ old('note') }}" class="form-control form-control-sm" placeholder="Misalnya: opname akhir bulan Juni 2026">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0"><i class="bi bi-list-check"></i> Daftar Produk</h6>
                <input type="text" id="opnameSearch" placeholder="Cari nama/kode..." class="form-control form-control-sm" style="max-width:240px">
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center" width="110">Stok Sistem</th>
                            <th class="text-center" width="130">Stok Fisik</th>
                            <th class="text-center" width="110">Selisih</th>
                            <th width="220">Catatan (opsional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $i => $product)
                            <tr class="opname-row" data-name="{{ strtolower($product->name) }}" data-code="{{ strtolower($product->code) }}">
                                <td>
                                    <div class="small text-muted" style="font-size:.7rem">{{ $product->code ?: '—' }}</div>
                                    <strong>{{ $product->name }}</strong>
                                    <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $product->id }}">
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $product->stock }}</span>
                                </td>
                                <td class="text-center">
                                    <input type="number" min="0"
                                           class="form-control form-control-sm text-center physical-input"
                                           name="items[{{ $i }}][qty_physical]"
                                           data-system="{{ $product->stock }}"
                                           value="">
                                </td>
                                <td class="text-center fw-bold diff-cell text-muted">0</td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="items[{{ $i }}][note]" placeholder="Detail jika ada selisih...">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada produk aktif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted" id="summary">Belum ada selisih.</small>
        <div class="d-flex gap-2">
            <a href="{{ route('stock-opnames.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
                <i class="bi bi-check-circle"></i> Simpan Opname
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const rows = Array.from(document.querySelectorAll('.opname-row'));
    const submitBtn = document.getElementById('submitBtn');
    const summary = document.getElementById('summary');
    const search = document.getElementById('opnameSearch');

    function recalc() {
        let counted = 0;
        let changed = 0;
        rows.forEach(row => {
            const input = row.querySelector('.physical-input');
            const system = parseInt(input.dataset.system);
            const cell = row.querySelector('.diff-cell');
            const val = input.value.trim();

            // Kosong = belum/tidak dihitung → tidak diproses.
            if (val === '') {
                cell.textContent = '—';
                cell.className = 'text-center fw-bold diff-cell text-muted';
                row.classList.remove('table-warning');
                return;
            }

            counted++;
            const physical = Math.max(0, parseInt(val) || 0);
            const diff = physical - system;
            cell.textContent = (diff > 0 ? '+' : '') + diff;
            cell.className = 'text-center fw-bold diff-cell ' +
                (diff > 0 ? 'text-success' : (diff < 0 ? 'text-danger' : 'text-muted'));
            row.classList.toggle('table-warning', diff !== 0);
            if (diff !== 0) changed++;
        });

        submitBtn.disabled = false;
        summary.textContent = counted === 0
            ? 'Isi stok fisik produk yang dihitung (yang dikosongkan tidak diproses).'
            : `${counted} produk dihitung` + (changed ? `, ${changed} ada selisih.` : '.');
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Simpan Opname';
    }

    rows.forEach(row => {
        row.querySelector('.physical-input').addEventListener('input', recalc);
    });

    // Saat simpan, kolom stok fisik yang dikosongkan otomatis diisi 0.
    document.getElementById('opnameForm').addEventListener('submit', () => {
        rows.forEach(row => {
            const input = row.querySelector('.physical-input');
            if (input.value.trim() === '') input.value = 0;
        });
    });

    search.addEventListener('input', () => {
        const q = search.value.toLowerCase().trim();
        rows.forEach(row => {
            const match = !q || row.dataset.name.includes(q) || row.dataset.code.includes(q);
            row.style.display = match ? '' : 'none';
        });
    });

    recalc();
})();
</script>
@endpush
@endsection
