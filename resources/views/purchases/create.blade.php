@extends('layouts.app')
@section('title', 'Pembelian Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Pembelian Baru dari Supplier</h5>
        <small class="text-muted">Catat barang yang masuk dari supplier — stok akan otomatis bertambah.</small>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">
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

<form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm">
    @csrf

    {{-- HEADER: supplier / tanggal / invoice --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplierSelect" class="form-select form-select-sm" required>
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tgl. Pembelian <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" id="purchaseDate" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">
                        Tgl. Tiba <span class="text-danger">*</span>
                        <i class="bi bi-info-circle" title="Stok bertambah pada tanggal ini. Kalau di masa depan, status PENDING dulu."></i>
                    </label>
                    <input type="date" name="arrival_date" id="arrivalDate" value="{{ old('arrival_date', now()->format('Y-m-d')) }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">No. Invoice</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" class="form-control form-control-sm" placeholder="INV-0001">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div id="arrivalNotice" class="small w-100" style="display:none"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- KIRI: KATALOG --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-grid"></i> Katalog Produk <span class="text-muted small fw-normal">— klik untuk tambah ke daftar beli</span></h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <input type="text" id="catalogSearch" placeholder="Cari nama atau kode produk..." class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <select id="catalogCategory" class="form-select form-select-sm">
                                <option value="">— Semua Kategori —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="position-relative">
                        <div id="catalogOverlay" class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center"
                             style="background: rgba(255,255,255,.92); z-index:10; border-radius: 6px;">
                            <i class="bi bi-truck text-warning" style="font-size: 3rem"></i>
                            <h6 class="mt-3 mb-1">Pilih Supplier Dulu</h6>
                            <p class="text-muted small mb-0">Katalog akan tampilkan produk dari supplier yang dipilih</p>
                        </div>
                    <div class="catalog-grid row g-2" id="catalogGrid" style="max-height: 520px; overflow-y: auto;">
                        @forelse($products as $product)
                            <div class="col-md-6 catalog-item"
                                 data-id="{{ $product->id }}"
                                 data-name="{{ $product->name }}"
                                 data-code="{{ $product->code }}"
                                 data-category="{{ $product->category_id }}"
                                 data-supplier="{{ $product->supplier_id ?? '' }}"
                                 data-purchase-price="{{ $product->purchase_price }}"
                                 data-stock="{{ $product->stock }}"
                                 data-image="{{ $product->image_url }}">
                                <div class="card border catalog-card h-100" role="button">
                                    <div class="card-body d-flex gap-2 align-items-center p-2">
                                        <img src="{{ $product->image_url }}" class="product-thumb">
                                        <div class="flex-grow-1 small">
                                            <div class="text-muted" style="font-size: .7rem">{{ $product->code ?: '—' }}</div>
                                            <div class="fw-semibold" style="line-height: 1.2">{{ \Illuminate\Support\Str::limit($product->name, 30) }}</div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <small class="text-muted">Stok: {{ $product->stock }}</small>
                                                <small class="text-primary fw-semibold">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Tidak ada produk aktif.
                            </div>
                        @endforelse
                    </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: DAFTAR BELI --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-list-ul"></i> Daftar Barang Dibeli</h6>

                    <table class="table table-sm align-middle" id="purchaseTable">
                        <thead class="small">
                            <tr>
                                <th>Produk</th>
                                <th width="65" class="text-center">Qty</th>
                                <th width="110">Harga/Unit</th>
                                <th width="30"></th>
                            </tr>
                        </thead>
                        <tbody id="purchaseBody">
                            <tr id="purchaseEmpty">
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="bi bi-basket"></i><br>
                                    <small>Belum ada barang. Klik produk di katalog.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between fw-bold border-bottom pb-2 mb-3">
                        <span>TOTAL</span>
                        <span class="text-success fs-5" id="grandTotalDisplay">Rp 0</span>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="update_purchase_price" value="0">
                        <input type="checkbox" name="update_purchase_price" value="1" id="updatePrice" class="form-check-input"
                               {{ old('update_purchase_price') ? 'checked' : '' }}>
                        <label for="updatePrice" class="form-check-label small">
                            Update harga beli master produk dengan harga ini
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Catatan (opsional)</label>
                        <textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Catatan pembelian...">{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-success w-100" disabled>
                        <i class="bi bi-check-circle"></i> Simpan Pembelian
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="purchaseInputs"></div>
</form>

@push('scripts')
<script>
(function () {
    const cart = new Map();
    const formatRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    const grid = document.getElementById('catalogGrid');
    const body = document.getElementById('purchaseBody');
    const empty = document.getElementById('purchaseEmpty');
    const inputs = document.getElementById('purchaseInputs');
    const grandTotalEl = document.getElementById('grandTotalDisplay');
    const submitBtn = document.getElementById('submitBtn');
    const searchInput = document.getElementById('catalogSearch');
    const categorySelect = document.getElementById('catalogCategory');

    function refresh() {
        body.innerHTML = '';
        inputs.innerHTML = '';

        if (cart.size === 0) {
            body.appendChild(empty);
            grandTotalEl.textContent = formatRp(0);
            submitBtn.disabled = true;
            return;
        }

        let total = 0;
        let idx = 0;
        cart.forEach((item, pid) => {
            const sub = item.qty * item.price;
            total += sub;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${item.image}" class="product-thumb" style="width:32px;height:32px">
                        <div class="small">
                            <div class="text-muted" style="font-size:.7rem">${item.code || '—'}</div>
                            <div class="fw-semibold" style="line-height:1.1">${item.name}</div>
                            <div class="text-muted" style="font-size:.7rem">Stok kini: ${item.stock} → ${item.stock + item.qty}</div>
                            <div class="text-muted small">Subtotal: <strong>${formatRp(sub)}</strong></div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center qty-input" min="1" value="${item.qty}">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-end price-input" min="0" step="100" value="${item.price}">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-btn"><i class="bi bi-x-circle"></i></button>
                </td>
            `;

            tr.querySelector('.qty-input').onchange = (e) => {
                item.qty = Math.max(1, parseInt(e.target.value) || 1);
                refresh();
            };
            tr.querySelector('.price-input').onchange = (e) => {
                item.price = Math.max(0, parseFloat(e.target.value) || 0);
                refresh();
            };
            tr.querySelector('.remove-btn').onclick = () => {
                cart.delete(pid);
                refresh();
            };
            body.appendChild(tr);

            inputs.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${idx}][product_id]" value="${pid}">
                 <input type="hidden" name="items[${idx}][qty]" value="${item.qty}">
                 <input type="hidden" name="items[${idx}][price]" value="${item.price}">`);
            idx++;
        });

        grandTotalEl.textContent = formatRp(total);
        submitBtn.disabled = false;
    }

    function addToCart(item) {
        if (cart.has(item.id)) {
            // Sudah ada di daftar → tambahkan qty
            cart.get(item.id).qty++;
        } else {
            cart.set(item.id, { ...item, qty: 1 });
        }
        refresh();
    }

    grid.addEventListener('click', (e) => {
        const card = e.target.closest('.catalog-card');
        if (!card) return;
        const wrapper = card.closest('.catalog-item');
        addToCart({
            id: parseInt(wrapper.dataset.id),
            name: wrapper.dataset.name,
            code: wrapper.dataset.code,
            price: parseFloat(wrapper.dataset.purchasePrice) || 0,
            stock: parseInt(wrapper.dataset.stock),
            image: wrapper.dataset.image,
        });
    });

    const supplierSelect = document.getElementById('supplierSelect');
    const catalogOverlay = document.getElementById('catalogOverlay');

    function applyFilter() {
        const q = searchInput.value.toLowerCase().trim();
        const cat = categorySelect.value;
        const sup = supplierSelect.value;

        // Tampilkan / sembunyikan overlay "Pilih Supplier Dulu"
        catalogOverlay.classList.toggle('d-none', !!sup);
        if (!sup) return; // gak perlu filter kalau katalog ke-cover overlay

        let visible = 0;
        document.querySelectorAll('.catalog-item').forEach(el => {
            const name = el.dataset.name.toLowerCase();
            const code = (el.dataset.code || '').toLowerCase();
            const matchQ = !q || name.includes(q) || code.includes(q);
            const matchCat = !cat || el.dataset.category === cat;
            const matchSup = el.dataset.supplier === sup;
            const show = matchQ && matchCat && matchSup;
            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Pesan kalau supplier dipilih tapi tidak ada produknya
        let emptyMsg = document.getElementById('catalogEmptyMsg');
        if (visible === 0) {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.id = 'catalogEmptyMsg';
                emptyMsg.className = 'col-12 text-center text-muted py-4';
                emptyMsg.innerHTML = '<i class="bi bi-inbox"></i> Tidak ada produk dari supplier ini.';
                document.getElementById('catalogGrid').appendChild(emptyMsg);
            }
            emptyMsg.style.display = '';
        } else if (emptyMsg) {
            emptyMsg.style.display = 'none';
        }
    }
    searchInput.addEventListener('input', applyFilter);
    categorySelect.addEventListener('change', applyFilter);
    supplierSelect.addEventListener('change', () => {
        // Saat ganti supplier (atau kembali ke kosong), clear keranjang
        if (cart.size > 0) {
            if (!confirm('Ganti supplier akan mengosongkan keranjang. Lanjutkan?')) {
                return; // batal ganti supplier? Sayangnya browser sudah ubah value... revert manual
            }
            cart.clear();
            refresh();
        }
        applyFilter();
    });

    // Indicator status berdasarkan tanggal tiba
    const arrivalDateInput = document.getElementById('arrivalDate');
    const arrivalNotice = document.getElementById('arrivalNotice');
    function updateArrivalNotice() {
        const today = new Date(); today.setHours(0,0,0,0);
        const arr = new Date(arrivalDateInput.value);
        arr.setHours(0,0,0,0);
        if (isNaN(arr)) { arrivalNotice.style.display='none'; return; }
        if (arr > today) {
            arrivalNotice.style.display = '';
            arrivalNotice.innerHTML = '<span class="badge badge-soft-warning"><i class="bi bi-clock"></i> Status akan PENDING — stok ditambah saat barang tiba</span>';
        } else {
            arrivalNotice.style.display = '';
            arrivalNotice.innerHTML = '<span class="badge badge-soft-success"><i class="bi bi-check-circle"></i> Status CONFIRMED — stok langsung bertambah</span>';
        }
    }
    arrivalDateInput.addEventListener('change', updateArrivalNotice);
    updateArrivalNotice();

    // INIT: jalankan filter & cart sekali saat load (untuk handle case supplier sudah ke-select dari awal)
    applyFilter();
    refresh();
})();
</script>
<style>
.catalog-card { transition: all .15s ease; cursor: pointer; }
.catalog-card:hover { border-color: #3b82f6 !important; box-shadow: 0 2px 8px rgba(59,130,246,.15); transform: translateY(-1px); }
</style>
@endpush
@endsection
