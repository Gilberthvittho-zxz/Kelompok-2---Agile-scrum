@csrf
<div class="mb-3">
    <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $category->name ?? '') }}" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" rows="3"
              class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="form-check form-switch mb-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
           {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Status: <strong>Aktif</strong></label>
    <div class="form-text">Kategori non-aktif tidak akan muncul di form tambah produk.</div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Simpan</button>
</div>
