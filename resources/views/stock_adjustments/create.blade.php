@extends('layouts.app')
@section('title', 'Adjustment Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Stock Adjustment Baru</h5>
        <small class="text-muted">Sesuaikan stok produk secara manual. Tiap produk bisa punya alasan berbeda.</small>
    </div>
    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-sm btn-outline-secondary">
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

<form method="POST" action="{{ route('stock-adjustments.store') }}" id="adjForm">
    @csrf

    {{-- HEADER: tanggal + catatan umum --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Tanggal <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="adjustment_date" value="{{ old('adjustment_date', now()->format('Y-m-d\TH:i')) }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label small text-muted">Catatan Umum (opsional)</label>
                    <input type="text" name="note" value="{{ old('note') }}" class="form-control form-control-sm" placeholder="Misalnya: hasil opname bulan Mei 2026">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- KIRI: KATALOG PRODUK --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-grid"></i> Katalog Produk <span class="text-muted small fw-normal">— klik untuk tambah</span></h6>

                    <input type="text" id="catalogSearch" placeholder="Cari nama/kode..." class="form-control form-control-sm mb-3">

                    <div id="catalogList" style="max-height: 540px; overflow-y: auto;">
                        @forelse($products as $product)
                            <div class="catalog-item border rounded p-2 mb-2 d-flex align-items-center gap-2"
                                 role="button"
                                 data-id="{{ $product->id }}"
                                 data-name="{{ $product->name }}"
                                 data-code="{{ $product->code }}"
                                 data-stock="{{ $product->stock }}"
                                 data-image="{{ $product->image_url }}">
                                <img src="{{ $product->image_url }}" class="product-thumb">
                                <div class="flex-grow-1 small">
                                    <div class="text-muted" style="font-size: .7rem">{{ $product->code ?: '—' }}</div>
                                    <div class="fw-semibold" style="line-height: 1.2">{{ \Illuminate\Support\Str::limit($product->name, 35) }}</div>
                                    <div class="text-muted" style="font-size: .75rem">Stok: <strong>{{ $product->stock }}</strong></div>
                                </div>
                                <i class="bi bi-plus-circle text-success"></i>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Tidak ada produk aktif.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: DAFTAR ADJUSTMENT --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted mb-0"><i class="bi bi-list-check"></i> Produk yang Disesuaikan</h6>
                        <small class="text-muted">Tiap produk bisa punya alasan & catatan berbeda</small>
                    </div>

                    <div id="adjList">
                        <div id="adjEmpty" class="text-center text-muted py-5 border rounded">
                            <i class="bi bi-arrow-left-circle"></i>
                            <div class="small mt-2">Pilih produk dari katalog di kiri untuk mulai menyesuaikan stok.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" id="submitBtn" class="btn btn-success" disabled>
                    <i class="bi bi-check-circle"></i> Simpan Adjustment
                </button>
            </div>
        </div>
    </div>

    <div id="adjInputs"></div>
</form>

@push('scripts')
<script>
(function () {
    const REASONS = @json($reasons);
    const cart = new Map();

    const catalogList = document.getElementById('catalogList');
    const adjList = document.getElementById('adjList');
    const adjEmpty = document.getElementById('adjEmpty');
    const inputs = document.getElementById('adjInputs');
    const submitBtn = document.getElementById('submitBtn');
    const searchInput = document.getElementById('catalogSearch');

    function buildReasonOptions(selected) {
        let html = '<option value="">— Pilih Alasan —</option>';
        for (const [k, v] of Object.entries(REASONS)) {
            html += `<option value="${k}" ${k === selected ? 'selected' : ''}>${v}</option>`;
        }
        return html;
    }

    function refresh() {
        adjList.innerHTML = '';
        inputs.innerHTML = '';

        if (cart.size === 0) {
            adjList.appendChild(adjEmpty);
            submitBtn.disabled = true;
            return;
        }

        let anyChanged = false;
        let allValid = true;
        let idx = 0;

        cart.forEach((item, pid) => {
            const diff = item.qtyAfter - item.stock;
            const hasChange = diff !== 0;
            if (hasChange) anyChanged = true;
            if (hasChange && !item.reason) allValid = false;

            const diffClass = diff > 0 ? 'text-success' : (diff < 0 ? 'text-danger' : 'text-muted');
            const diffSign = diff > 0 ? '+' : '';
            const rowBg = hasChange ? 'border-primary' : '';

            const div = document.createElement('div');
            div.className = `border rounded p-3 mb-2 ${rowBg}`;
            div.innerHTML = `
                <div class="d-flex align-items-start gap-2 mb-2">
                    <img src="${item.image}" class="product-thumb" style="width:36px;height:36px">
                    <div class="flex-grow-1">
                        <div class="text-muted small" style="font-size:.7rem">${item.code || '—'}</div>
                        <div class="fw-semibold">${item.name}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-btn" title="Hapus">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>

                <div class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Stok Lama</label>
                        <div class="form-control form-control-sm bg-light text-center">${item.stock}</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Stok Baru</label>
                        <input type="number" class="form-control form-control-sm text-center qty-input" min="0" value="${item.qtyAfter}">
                    </div>
                    <div class="col-md-2 text-center">
                        <label class="form-label small text-muted mb-1">Perubahan</label>
                        <div class="fw-bold ${diffClass} mt-1">${diffSign}${diff}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">
                            Alasan ${hasChange ? '<span class="text-danger">*</span>' : ''}
                        </label>
                        <select class="form-select form-select-sm reason-select" ${hasChange ? 'required' : ''}>
                            ${buildReasonOptions(item.reason)}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">
                            Catatan ${item.reason === 'lain' ? '<span class="text-danger">*</span>' : '(opsional)'}
                        </label>
                        <input type="text" class="form-control form-control-sm note-input"
                               placeholder="${item.reason === 'lain' ? 'Wajib' : 'Detail...'}"
                               value="${item.note || ''}">
                    </div>
                </div>
            `;

            div.querySelector('.qty-input').onchange = (e) => {
                item.qtyAfter = Math.max(0, parseInt(e.target.value) || 0);
                refresh();
            };
            div.querySelector('.reason-select').onchange = (e) => {
                item.reason = e.target.value;
                refresh();
            };
            div.querySelector('.note-input').onchange = (e) => {
                item.note = e.target.value;
            };
            div.querySelector('.remove-btn').onclick = () => {
                cart.delete(pid);
                refresh();
            };
            adjList.appendChild(div);

            if (hasChange) {
                inputs.insertAdjacentHTML('beforeend',
                    `<input type="hidden" name="items[${idx}][product_id]" value="${pid}">
                     <input type="hidden" name="items[${idx}][qty_after]" value="${item.qtyAfter}">
                     <input type="hidden" name="items[${idx}][reason]" value="${item.reason || ''}">
                     <input type="hidden" name="items[${idx}][note]" value="${item.note || ''}">`);
                idx++;
            }
        });

        submitBtn.disabled = !anyChanged || !allValid;

        // Hint button text
        if (!anyChanged) {
            submitBtn.innerHTML = '<i class="bi bi-info-circle"></i> Ubah stok minimal 1 produk';
        } else if (!allValid) {
            submitBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Lengkapi alasan untuk semua produk';
        } else {
            submitBtn.innerHTML = `<i class="bi bi-check-circle"></i> Simpan Adjustment (${idx} produk)`;
        }
    }

    catalogList.addEventListener('click', (e) => {
        const item = e.target.closest('.catalog-item');
        if (!item) return;
        const pid = parseInt(item.dataset.id);
        if (cart.has(pid)) {
            // sudah ada → scroll ke item itu di kanan
            alert('Produk sudah ada di daftar.');
            return;
        }
        const stock = parseInt(item.dataset.stock);
        cart.set(pid, {
            id: pid,
            name: item.dataset.name,
            code: item.dataset.code,
            image: item.dataset.image,
            stock: stock,
            qtyAfter: stock,    // default sama dengan stok awal (user ubah dari sini)
            reason: '',
            note: '',
        });
        refresh();
    });

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase().trim();
        document.querySelectorAll('.catalog-item').forEach(el => {
            const name = el.dataset.name.toLowerCase();
            const code = (el.dataset.code || '').toLowerCase();
            el.style.display = (!q || name.includes(q) || code.includes(q)) ? '' : 'none';
        });
    });

    refresh();
})();
</script>
<style>
.catalog-item { transition: all .15s ease; }
.catalog-item:hover { border-color: #3b82f6 !important; background: #f0f7ff; }
</style>
@endpush
@endsection
