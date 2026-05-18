@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $supplier->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Nama PIC / Kontak Person</label>
        <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
               value="{{ old('contact_person', $supplier->contact_person ?? '') }}">
        @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $supplier->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Telepon</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $supplier->phone ?? '') }}">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Alamat</label>
        <textarea name="address" rows="3"
                  class="form-control @error('address') is-invalid @enderror">{{ old('address', $supplier->address ?? '') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                   {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">Status: <strong>Aktif</strong></label>
            <div class="form-text">Supplier non-aktif tidak akan muncul di form tambah produk.</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Simpan</button>
</div>
