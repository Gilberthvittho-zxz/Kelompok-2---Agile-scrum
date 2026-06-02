<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MOTOKU') - {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-w: 240px; }
        body { background: #f5f7fb; }

        /* ===== Sidebar ===== */
        .app-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: #1f2937;
            color: #d1d5db;
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform .25s ease;
        }
        .app-sidebar .brand {
            padding: 1.25rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
            font-weight: 700;
            letter-spacing: .5px;
            font-size: 1.25rem;
            color: #fff;
        }
        .app-sidebar .brand small { display:block; font-size:.7rem; font-weight:400; color:#9ca3af; letter-spacing:.3px; }
        .app-sidebar .nav-section { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; padding: 1rem 1.25rem .5rem; }
        .app-sidebar .nav-link {
            color: #cbd5e1;
            padding: .65rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: .92rem;
            border-left: 3px solid transparent;
        }
        .app-sidebar .nav-link i { font-size: 1.05rem; width: 18px; text-align: center; }
        .app-sidebar .nav-link:hover { background: rgba(255,255,255,.04); color: #fff; }
        .app-sidebar .nav-link.active {
            background: rgba(59,130,246,.12);
            color: #fff;
            border-left-color: #3b82f6;
            font-weight: 600;
        }
        .app-sidebar .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.06);
            font-size: .85rem;
        }
        .app-sidebar .sidebar-footer .user-name { color: #fff; font-weight: 600; font-size: .9rem; }
        .app-sidebar .sidebar-footer .logout-btn {
            background: transparent;
            border: 0;
            color: #f87171;
            padding: 0;
            margin-top: .25rem;
            font-size: .85rem;
        }
        .app-sidebar .sidebar-footer .logout-btn:hover { color: #fca5a5; }

        /* ===== Main content ===== */
        .app-main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .app-main--guest { margin-left: 0; }
        .topbar {
            background: #fff;
            padding: .75rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 1030;
        }
        .topbar .sidebar-toggle { display: none; background: transparent; border: 0; font-size: 1.4rem; color: #374151; }

        /* ===== Mobile drawer ===== */
        @media (max-width: 991.98px) {
            .app-sidebar { transform: translateX(-100%); }
            .app-sidebar.is-open { transform: translateX(0); box-shadow: 0 0 30px rgba(0,0,0,.3); }
            .app-main { margin-left: 0; }
            .topbar .sidebar-toggle { display: inline-flex; }
            .sidebar-backdrop {
                position: fixed; inset: 0; background: rgba(0,0,0,.4);
                z-index: 1039; display: none;
            }
            .sidebar-backdrop.is-open { display: block; }
        }

        /* ===== Utility ===== */
        .stat-card { border: 0; border-radius: 12px; }
        .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; }
        .table thead th { background: #f1f3f7; font-weight: 600; font-size: .85rem; text-transform: uppercase; letter-spacing: .5px; }
        .badge-soft-warning { background: #fff3cd; color: #b58105; }
        .badge-soft-danger  { background: #f8d7da; color: #842029; }
        .badge-soft-success { background: #d1e7dd; color: #0a3622; }
        .product-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e3e7ee; }

        /* ===== Inline switch (untuk action bar) ===== */
        .switch-sm {
            position: relative;
            width: 38px;
            height: 22px;
            display: inline-block;
            cursor: pointer;
            vertical-align: middle;
        }
        .switch-sm input { opacity: 0; width: 0; height: 0; pointer-events: none; }
        .switch-sm:focus-visible { outline: 2px solid #93c5fd; outline-offset: 2px; border-radius: 999px; }
        .switch-sm .slider {
            position: absolute;
            inset: 0;
            background: #cbd5e1;
            border-radius: 999px;
            transition: background .2s ease;
        }
        .switch-sm .slider::before {
            content: "";
            position: absolute;
            left: 2px; top: 2px;
            width: 18px; height: 18px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 2px rgba(0,0,0,.25);
            transition: transform .2s ease;
        }
        .switch-sm input:checked + .slider { background: #22c55e; }
        .switch-sm input:checked + .slider::before { transform: translateX(16px); }

        /* ===== Card footer action bar ===== */
        .action-footer {
            background: #fafbfc;
            border-top: 1px solid #e5e7eb;
            padding: .75rem 1.25rem;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            justify-content: space-between;
            align-items: center;
        }
        .action-footer .status-inline { display: flex; align-items: center; gap: .5rem; font-size: .875rem; }
        .action-footer .status-inline .label-status { color: #6b7280; }
        .action-footer .status-inline .value-status { font-weight: 600; }
        .action-footer .actions { display: flex; gap: .5rem; }
    </style>
</head>
<body>
    @auth
    <aside class="app-sidebar" id="appSidebar">
        <div class="brand">
            <i class="bi bi-tools"></i> MOTOKU
            <small>Inventory Sparepart Motor</small>
        </div>

        <div class="nav-section">Menu Utama</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </nav>

        <div class="nav-section">Data Master</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                <i class="bi bi-tags"></i> Kategori
            </a>
            <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                <i class="bi bi-truck"></i> Supplier
            </a>
            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="bi bi-box-seam"></i> Produk
            </a>
        </nav>

        <div class="nav-section">Transaksi</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                <i class="bi bi-cart-check"></i> Penjualan
            </a>
            <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                <i class="bi bi-box-arrow-in-down"></i> Pembelian
            </a>
        </nav>

        <div class="nav-section">Inventory</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('stocks.*') ? 'active' : '' }}" href="{{ route('stocks.index') }}">
                <i class="bi bi-clipboard-data"></i> Stok
            </a>
            <a class="nav-link {{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}" href="{{ route('stock-adjustments.index') }}">
                <i class="bi bi-arrow-repeat"></i> Stock Adjustment
            </a>
        </nav>

        <div class="sidebar-footer">
            <i class="bi bi-person-circle"></i>
            <span class="user-name">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    @endauth

    <div class="app-main @guest app-main--guest @endguest">
        @auth
        <div class="topbar">
            <button class="sidebar-toggle" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="ms-auto text-muted small">
                <i class="bi bi-calendar"></i> {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
        @endauth

        <main class="container-fluid px-4 py-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggle = document.getElementById('sidebarToggle');
            if (!sidebar || !toggle) return;

            const close = () => { sidebar.classList.remove('is-open'); backdrop.classList.remove('is-open'); };
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('is-open');
                backdrop.classList.toggle('is-open');
            });
            backdrop.addEventListener('click', close);
        })();
    </script>
    @stack('scripts')
</body>
</html>
