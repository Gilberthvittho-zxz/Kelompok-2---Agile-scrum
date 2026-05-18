@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Kode SKU</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $product->code ?? '') }}" placeholder="OPT001">
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $product->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Kategori <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">— Pilih Kategori —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
            <option value="">— Tanpa Supplier —</option>
            @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}" @selected(old('supplier_id', $product->supplier_id ?? '') == $sup->id)>
                    {{ $sup->name }}
                </option>
            @endforeach
        </select>
        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" name="purchase_price" step="0.01" min="0"
                   class="form-control @error('purchase_price') is-invalid @enderror"
                   value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" required>
            @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" name="selling_price" step="0.01" min="0"
                   class="form-control @error('selling_price') is-invalid @enderror"
                   value="{{ old('selling_price', $product->selling_price ?? 0) }}" required>
            @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-2">
        <label class="form-label">Stok Awal <span class="text-danger">*</span></label>
        <input type="number" name="stock" min="0"
               class="form-control @error('stock') is-invalid @enderror"
               value="{{ old('stock', $product->stock ?? 0) }}" required>
        @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Stok Min. <span class="text-danger">*</span></label>
        <input type="number" name="min_stock" min="0"
               class="form-control @error('min_stock') is-invalid @enderror"
               value="{{ old('min_stock', $product->min_stock ?? 5) }}" required>
        @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" rows="2"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Gambar Produk</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
               class="form-control @error('image') is-invalid @enderror">
        <small class="text-muted">JPG/PNG/WEBP, maks 2 MB</small>
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @isset($product)
        @if($product->image)
            <div class="col-md-6">
                <label class="form-label">Gambar Saat Ini</label>
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $product->image_url }}" alt="" class="product-thumb" style="width: 64px; height: 64px;">
                    <div class="form-check">
                        <input type="checkbox" name="remove_image" value="1" id="remove_image" class="form-check-input">
                        <label for="remove_image" class="form-check-label text-danger">Hapus gambar</label>
                    </div>
                </div>
            </div>
        @endif
    @endisset

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                   {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">Status: <strong>Aktif</strong></label>
            <div class="form-text">Produk non-aktif tidak akan muncul di transaksi penjualan/pembelian nanti.</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Simpan</button>
</div>
