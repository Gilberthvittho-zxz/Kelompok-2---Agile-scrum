@push('scripts')
<script>
    // Dropdown sub-menu: ganti panel sesuai pilihan.
    document.querySelectorAll('.submenu-select').forEach(sel => {
        sel.addEventListener('change', () => {
            const container = document.querySelector(sel.dataset.content);
            if (!container) return;
            container.querySelectorAll(':scope > .tab-pane').forEach(p => p.classList.remove('show', 'active'));
            const target = document.querySelector(sel.value);
            if (target) target.classList.add('show', 'active');
        });
    });
</script>
<style>
    /* Konten laporan dibuat lebih lebar */
    .app-main main.container-fluid { padding-left: 1rem !important; padding-right: 1rem !important; }

    .report-row .bi-chevron-down { transition: transform .2s ease; }
    .report-row[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }

    .submenu-select { font-weight: 600; color: #334155; border-color: #cbd5e1; }
    .submenu-select:focus { border-color: #0f766e; box-shadow: 0 0 0 .2rem rgba(15,118,110,.15); }

    @media print {
        .no-print, .app-sidebar, .topbar, .sidebar-backdrop { display: none !important; }
        .app-main { margin-left: 0 !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
        .tab-content > .tab-pane { display: block !important; opacity: 1 !important; margin-bottom: 1.5rem; }
    }
</style>
@endpush
