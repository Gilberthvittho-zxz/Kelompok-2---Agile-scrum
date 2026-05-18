<div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="passwordConfirmForm" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="passwordConfirmMethod" value="PATCH">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordConfirmTitle">
                    <i class="bi bi-shield-lock text-primary"></i> Konfirmasi Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="passwordConfirmMessage">
                    Masukkan password Anda untuk melanjutkan.
                </p>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                    <input type="password" name="confirm_password" class="form-control"
                           placeholder="Password login Anda" required autocomplete="current-password">
                </div>
                <small class="text-muted mt-2 d-block">
                    <i class="bi bi-info-circle"></i> Password yang sama dengan saat login.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="passwordConfirmSubmit">
                    <i class="bi bi-check-lg"></i> Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('passwordConfirmModal');
    const form = document.getElementById('passwordConfirmForm');
    const methodInput = document.getElementById('passwordConfirmMethod');
    const titleEl = document.getElementById('passwordConfirmTitle');
    const msgEl = document.getElementById('passwordConfirmMessage');
    const submitBtn = document.getElementById('passwordConfirmSubmit');
    const pwInput = form.querySelector('input[name="confirm_password"]');
    const bsModal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('[data-confirm-action]').forEach(el => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            form.action = el.dataset.confirmAction;
            methodInput.value = el.dataset.confirmMethod || 'PATCH';
            titleEl.innerHTML = '<i class="bi bi-shield-lock text-primary"></i> ' +
                (el.dataset.confirmTitle || 'Konfirmasi Password');
            msgEl.textContent = el.dataset.confirmMessage || 'Masukkan password Anda untuk melanjutkan.';
            submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> ' + (el.dataset.confirmSubmit || 'Konfirmasi');
            submitBtn.className = 'btn ' + (el.dataset.confirmClass || 'btn-primary');
            pwInput.value = '';
            bsModal.show();
        });
    });

    modalEl.addEventListener('shown.bs.modal', () => pwInput.focus());
})();
</script>
@endpush
