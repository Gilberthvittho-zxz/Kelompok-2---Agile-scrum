@extends('layouts.app')
@section('title', 'Transaksi Penjualan Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Transaksi Penjualan Baru</h5>
        <small class="text-muted">Pilih produk dari katalog, atur qty, lalu konfirmasi pembayaran.</small>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
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

<form method="POST" action="{{ route('sales.store') }}" id="saleForm">
    @csrf
    <div class="row g-3">
        {{-- LEFT: KATALOG PRODUK --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-grid"></i> Katalog Produk</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <input type="text" id="catalogSearch" placeholder="Cari nama atau kode produk..."
                                   class="form-control form-control-sm">
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

                    <div class="catalog-grid row g-2" id="catalogGrid" style="max-height: 460px; overflow-y: auto;">
                        @forelse($products as $product)
                            <div class="col-md-6 catalog-item"
                                 data-id="{{ $product->id }}"
                                 data-name="{{ $product->name }}"
                                 data-code="{{ $product->code }}"
                                 data-category="{{ $product->category_id }}"
                                 data-price="{{ $product->selling_price }}"
                                 data-stock="{{ $product->stock }}"
                                 data-image="{{ $product->image_url }}">
                                <div class="card border catalog-card h-100" role="button">
                                    <div class="card-body d-flex gap-2 align-items-center p-2">
                                        <img src="{{ $product->image_url }}" class="product-thumb">
                                        <div class="flex-grow-1 small">
                                            <div class="text-muted" style="font-size: .7rem">{{ $product->code ?: '—' }}</div>
                                            <div class="fw-semibold" style="line-height: 1.2">{{ \Illuminate\Support\Str::limit($product->name, 30) }}</div>
                                            <div class="text-success fw-bold mt-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
                                            <div><small class="text-muted">Stok: {{ $product->stock }}</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Tidak ada produk aktif dengan stok tersedia.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: KERANJANG & CHECKOUT --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-basket"></i> Keranjang</h6>

                    <table class="table table-sm align-middle" id="cartTable">
                        <thead class="small">
                            <tr>
                                <th>Produk</th>
                                <th width="80" class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th width="32"></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody">
                            <tr id="cartEmpty"><td colspan="4" class="text-center text-muted py-3">Keranjang kosong. Klik produk di katalog.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small text-muted">Nama Pelanggan (opsional)</label>
                        <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ old('customer_name') }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted">Metode Pembayaran <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($paymentMethods as $i => $pm)
                                <input type="radio" class="btn-check" name="payment_method_id"
                                       id="pm{{ $pm->id }}" value="{{ $pm->id }}"
                                       {{ old('payment_method_id', $paymentMethods->first()->id) == $pm->id ? 'checked' : '' }} required>
                                <label class="btn btn-sm btn-outline-secondary" for="pm{{ $pm->id }}">
                                    <i class="bi {{ $pm->icon ?? 'bi-credit-card' }}"></i> {{ $pm->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span id="displaySubtotal">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="text-muted">Diskon</span>
                        <div class="input-group input-group-sm" style="width: 140px">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="discount" id="discountInput" class="form-control form-control-sm text-end"
                                   value="{{ old('discount', 0) }}" min="0" step="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2">
                        <span>TOTAL</span>
                        <span class="text-success fs-5" id="displayTotal">Rp 0</span>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small text-muted">Jumlah Dibayar <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="paid_amount" id="paidInput" class="form-control form-control-sm text-end"
                                   value="{{ old('paid_amount', 0) }}" min="0" step="1000" required>
                        </div>
                        <div class="d-flex justify-content-between small mt-2">
                            <span class="text-muted">Kembalian</span>
                            <span id="displayChange" class="fw-semibold">Rp 0</span>
                        </div>
                    </div>

                    <div class="mt-2">
                        <textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Catatan (opsional)">{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-success w-100 mt-3" disabled>
                        <i class="bi bi-check-circle"></i> Konfirmasi & Simpan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden cart items will be injected here --}}
    <div id="cartHiddenInputs"></div>
</form>

@push('scripts')
<script>
(function () {
    const cart = new Map(); // productId -> {name, code, price, qty, stock, image}
    const formatRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    const grid = document.getElementById('catalogGrid');
    const cartBody = document.getElementById('cartBody');
    const cartEmpty = document.getElementById('cartEmpty');
    const hiddenInputs = document.getElementById('cartHiddenInputs');
    const subtotalEl = document.getElementById('displaySubtotal');
    const totalEl = document.getElementById('displayTotal');
    const changeEl = document.getElementById('displayChange');
    const discountInput = document.getElementById('discountInput');
    const paidInput = document.getElementById('paidInput');
    const submitBtn = document.getElementById('submitBtn');
    const searchInput = document.getElementById('catalogSearch');
    const categorySelect = document.getElementById('catalogCategory');

    function refreshCart() {
        cartBody.innerHTML = '';
        hiddenInputs.innerHTML = '';

        if (cart.size === 0) {
            cartBody.appendChild(cartEmpty);
        }

        let subtotal = 0;
        let idx = 0;
        cart.forEach((item, pid) => {
            const lineSub = item.price * item.qty;
            subtotal += lineSub;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${item.image}" class="product-thumb" style="width:32px;height:32px">
                        <div class="small">
                            <div class="text-muted" style="font-size:.7rem">${item.code || '—'}</div>
                            <div class="fw-semibold" style="line-height:1.1">${item.name}</div>
                            <div class="text-muted" style="font-size:.7rem">${formatRp(item.price)} × ${item.qty}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <div class="input-group input-group-sm" style="width:90px;margin:auto">
                        <button type="button" class="btn btn-outline-secondary qty-dec">−</button>
                        <input type="number" class="form-control text-center qty-input" min="1" max="${item.stock}" value="${item.qty}">
                        <button type="button" class="btn btn-outline-secondary qty-inc">+</button>
                    </div>
                </td>
                <td class="text-end small fw-semibold">${formatRp(lineSub)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-btn"><i class="bi bi-x-circle"></i></button>
                </td>
            `;

            tr.querySelector('.qty-dec').onclick = () => {
                if (item.qty > 1) { item.qty--; refreshCart(); }
            };
            tr.querySelector('.qty-inc').onclick = () => {
                if (item.qty < item.stock) { item.qty++; refreshCart(); }
                else alert(`Stok ${item.name} hanya tersedia ${item.stock}`);
            };
            tr.querySelector('.qty-input').onchange = (e) => {
                let v = parseInt(e.target.value) || 1;
                if (v < 1) v = 1;
                if (v > item.stock) { v = item.stock; alert(`Stok ${item.name} hanya tersedia ${item.stock}`); }
                item.qty = v;
                refreshCart();
            };
            tr.querySelector('.remove-btn').onclick = () => {
                cart.delete(pid);
                refreshCart();
            };

            cartBody.appendChild(tr);

            // Hidden inputs untuk form submit
            hiddenInputs.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${idx}][product_id]" value="${pid}">
                 <input type="hidden" name="items[${idx}][qty]" value="${item.qty}">`);
            idx++;
        });

        const discount = parseFloat(discountInput.value) || 0;
        const total = Math.max(0, subtotal - discount);
        const paid = parseFloat(paidInput.value) || 0;
        const change = paid - total;

        subtotalEl.textContent = formatRp(subtotal);
        totalEl.textContent = formatRp(total);
        changeEl.textContent = formatRp(Math.max(0, change));
        changeEl.classList.toggle('text-danger', paid < total && cart.size > 0);
        changeEl.classList.toggle('text-success', paid >= total && cart.size > 0);

        submitBtn.disabled = cart.size === 0 || paid < total;
    }

    function addToCart(item) {
        if (cart.has(item.id)) {
            const exist = cart.get(item.id);
            if (exist.qty < exist.stock) exist.qty++;
            else alert(`Stok ${exist.name} hanya tersedia ${exist.stock}`);
        } else {
            cart.set(item.id, { ...item, qty: 1 });
        }
        refreshCart();
    }

    grid.addEventListener('click', (e) => {
        const card = e.target.closest('.catalog-card');
        if (!card) return;
        const wrapper = card.closest('.catalog-item');
        addToCart({
            id: parseInt(wrapper.dataset.id),
            name: wrapper.dataset.name,
            code: wrapper.dataset.code,
            price: parseFloat(wrapper.dataset.price),
            stock: parseInt(wrapper.dataset.stock),
            image: wrapper.dataset.image,
        });
    });

    function applyFilter() {
        const q = searchInput.value.toLowerCase().trim();
        const cat = categorySelect.value;
        document.querySelectorAll('.catalog-item').forEach(el => {
            const name = el.dataset.name.toLowerCase();
            const code = (el.dataset.code || '').toLowerCase();
            const matchQ = !q || name.includes(q) || code.includes(q);
            const matchCat = !cat || el.dataset.category === cat;
            el.style.display = (matchQ && matchCat) ? '' : 'none';
        });
    }
    searchInput.addEventListener('input', applyFilter);
    categorySelect.addEventListener('change', applyFilter);

    discountInput.addEventListener('input', refreshCart);
    paidInput.addEventListener('input', refreshCart);

    refreshCart();
})();
</script>
<style>
.catalog-card { transition: all .15s ease; cursor: pointer; }
.catalog-card:hover { border-color: #3b82f6 !important; box-shadow: 0 2px 8px rgba(59,130,246,.15); transform: translateY(-1px); }
.btn-check:checked + .btn-outline-secondary { background: #1f2937; color: white; border-color: #1f2937; }
</style>
@endpush
@endsection
