@extends('layouts.app')
@section('title', 'Adjustment Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Stock Adjustment Baru</h5>
        <small class="text-muted">Sesuaikan stok produk secara manual. Sistem akan mencatat perubahan untuk audit trail.</small>
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

<form method="POST" action="{{ route('stock-adjustments.store') }}">
    @csrf
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label small">Tanggal <span class="text-danger">*</span></label>
            <input type="datetime-local" name="adjustment_date" value="{{ old('adjustment_date', now()->format('Y-m-d\TH:i')) }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Alasan <span class="text-danger">*</span></label>
            <select name="reason" id="reasonSelect" class="form-select" required>
                <option value="">— Pilih Alasan —</option>
                @foreach($reasons as $k => $label)
                    <option value="{{ $k }}" @selected(old('reason') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small">
                Catatan
                <span id="noteRequired" class="text-danger" style="display:none">*</span>
            </label>
            <input type="text" name="note" value="{{ old('note') }}" class="form-control" placeholder="Detail koreksi...">
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0"><i class="bi bi-list-ul"></i> Produk yang Disesuaikan</h6>
                <div class="d-flex gap-2">
                    <select id="productPicker" class="form-select form-select-sm" style="width: 320px">
                        <option value="">— Pilih Produk —</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-stock="{{ $product->stock }}"
                                data-image="{{ $product->image_url }}">
                                {{ $product->code ? '['.$product->code.'] ' : '' }}{{ $product->name }} (stok: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="addProductBtn" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Tambah</button>
                </div>
            </div>

            <table class="table table-sm align-middle">
                <thead class="small">
                    <tr>
                        <th>Produk</th>
                        <th width="120" class="text-center">Stok Lama</th>
                        <th width="140" class="text-center">Stok Baru</th>
                        <th width="120" class="text-center">Perubahan</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody id="adjBody">
                    <tr id="adjEmpty"><td colspan="5" class="text-center text-muted py-3">Belum ada produk. Pilih dari dropdown di atas.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" id="submitBtn" class="btn btn-success" disabled>
            <i class="bi bi-check-circle"></i> Simpan Adjustment
        </button>
    </div>

    <div id="adjInputs"></div>
</form>

@push('scripts')
<script>
(function () {
    const cart = new Map();
    const body = document.getElementById('adjBody');
    const empty = document.getElementById('adjEmpty');
    const picker = document.getElementById('productPicker');
    const addBtn = document.getElementById('addProductBtn');
    const inputs = document.getElementById('adjInputs');
    const submitBtn = document.getElementById('submitBtn');
    const reasonSelect = document.getElementById('reasonSelect');
    const noteRequired = document.getElementById('noteRequired');

    function refresh() {
        body.innerHTML = '';
        inputs.innerHTML = '';
        if (cart.size === 0) { body.appendChild(empty); submitBtn.disabled = true; return; }

        let anyChanged = false;
        let idx = 0;
        cart.forEach((item, pid) => {
            const diff = item.qtyAfter - item.stock;
            if (diff !== 0) anyChanged = true;
            const diffClass = diff > 0 ? 'text-success' : (diff < 0 ? 'text-danger' : 'text-muted');
            const diffSign = diff > 0 ? '+' : '';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${item.image}" class="product-thumb" style="width:32px;height:32px">
                        <div class="small">
                            <div class="text-muted" style="font-size:.7rem">${item.code || '—'}</div>
                            <div class="fw-semibold">${item.name}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center"><span class="badge bg-light text-dark">${item.stock}</span></td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center qty-input" min="0" value="${item.qtyAfter}">
                </td>
                <td class="text-center fw-bold ${diffClass}">${diffSign}${diff}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-btn"><i class="bi bi-x-circle"></i></button>
                </td>
            `;

            tr.querySelector('.qty-input').onchange = (e) => {
                item.qtyAfter = Math.max(0, parseInt(e.target.value) || 0);
                refresh();
            };
            tr.querySelector('.remove-btn').onclick = () => { cart.delete(pid); refresh(); };
            body.appendChild(tr);

            inputs.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${idx}][product_id]" value="${pid}">
                 <input type="hidden" name="items[${idx}][qty_after]" value="${item.qtyAfter}">`);
            idx++;
        });

        submitBtn.disabled = !anyChanged;
    }

    addBtn.onclick = () => {
        if (!picker.value) { alert('Pilih produk dulu.'); return; }
        const opt = picker.options[picker.selectedIndex];
        const pid = parseInt(picker.value);
        if (cart.has(pid)) { alert('Produk sudah ada di daftar.'); return; }
        const stock = parseInt(opt.dataset.stock);
        cart.set(pid, {
            name: opt.dataset.name,
            code: opt.dataset.code,
            image: opt.dataset.image,
            stock: stock,
            qtyAfter: stock, // default sama dengan stok awal
        });
        picker.selectedIndex = 0;
        refresh();
    };

    reasonSelect.onchange = () => {
        noteRequired.style.display = reasonSelect.value === 'lain' ? '' : 'none';
    };

    refresh();
})();
</script>
@endpush
@endsection
