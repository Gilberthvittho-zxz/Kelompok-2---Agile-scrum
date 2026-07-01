{{--
    Modal import data master via Excel (.xlsx).
    Variabel: $entity, $importRoute, $templateRoute, $columns
--}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ $importRoute }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Import {{ $entity }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">
                    Upload file <strong>Excel (.xlsx)</strong> berisi data {{ strtolower($entity) }}.
                    Urutan kolom: <code>{{ $columns }}</code>.
                </p>
                <p class="small mb-3">
                    Belum punya formatnya?
                    <a href="{{ $templateRoute }}"><i class="bi bi-file-earmark-excel"></i> Download template Excel</a> dulu, isi, lalu upload di sini.
                </p>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control @error('file') is-invalid @enderror" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Data yang nama/kode-nya sudah ada akan diperbarui (bukan duplikat).</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Import Sekarang</button>
            </div>
        </form>
    </div>
</div>

@error('file')
    @push('scripts')
    <script>
        // Buka kembali modal import bila ada error validasi file.
        document.addEventListener('DOMContentLoaded', () => {
            const m = document.getElementById('importModal');
            if (m && window.bootstrap) new bootstrap.Modal(m).show();
        });
    </script>
    @endpush
@enderror
