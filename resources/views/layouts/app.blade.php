<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pendataan Advertorial')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e3a5f;
            --primary-light: #2c5282;
            --accent: #3182ce;
            --bg: #f4f6f9;
            --card-bg: #ffffff;
            --text: #1a202c;
            --muted: #718096;
            --success: #38a169;
            --warning: #d69e2e;
            --danger: #e53e3e;
            --sidebar-width: 240px;
            --sidebar-collapsed-width: 70px;
        }

        * { font-family: 'Inter', sans-serif; }

        body { background-color: var(--bg); color: var(--text); }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary);
            padding: 1.5rem 1rem;
            overflow-y: auto;
            transition: width 0.2s ease;
            z-index: 1000;
        }

        .sidebar.collapsed { width: var(--sidebar-collapsed-width); padding: 1.5rem 0.6rem; }

        .sidebar .brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar.collapsed .brand-text { display: none; }

        .sidebar-toggle-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            width: 100%;
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-toggle-btn:hover { background: rgba(255,255,255,0.2); }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.65rem 0.9rem;
            border-radius: 8px;
            font-size: 0.92rem;
            font-weight: 500;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-link { justify-content: center; padding: 0.65rem; }
        .sidebar.collapsed .link-label { display: none; }

        .sidebar .nav-link:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar .nav-link.active { background: var(--accent); color: #fff; }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.2s ease;
        }

        .main-content.collapsed { margin-left: var(--sidebar-collapsed-width); }

        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; }
        .topbar h1 { font-size: 1.4rem; font-weight: 700; color: var(--text); margin: 0; }

        .card-custom {
            background: var(--card-bg);
            border: none;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            padding: 1.5rem;
        }

        .table-custom thead { background: #f8fafc; }
        .table-custom thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--muted);
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .table-custom tbody td { font-size: 0.9rem; vertical-align: middle; }

        .badge-status { padding: 0.4em 0.8em; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-sudah { background: #def7ec; color: var(--success); }
        .badge-belum { background: #fef3c7; color: #b45309; }
        .badge-jatuh { background: #fde2e1; color: var(--danger); }

        .btn-primary-custom { background: var(--primary); color: #fff; border: none; border-radius: 8px; font-weight: 500; padding: 0.55rem 1.1rem; }
        .btn-primary-custom:hover { background: var(--primary-light); color: #fff; }

        .form-label { font-weight: 600; font-size: 0.87rem; color: var(--text); }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(49,130,206,0.12); }

        .kpi-card { padding: 1.25rem; }
        .kpi-label { font-size: 0.82rem; color: var(--muted); font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem; justify-content: center; }
        .kpi-value { font-size: 1.5rem; font-weight: 700; color: var(--text); }
        .kpi-success .kpi-value { color: var(--success); }
        .kpi-warning .kpi-value { color: var(--danger); }

        .kpi-card-link { text-decoration: none; color: inherit; display: block; transition: transform 0.15s ease, box-shadow 0.15s ease; }
        .kpi-card-link:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

        .list-pagination { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; padding-top:16px; border-top:1px solid #e8eef5; margin-top:8px; }
        .list-pagination-summary { margin:0; color:#718096; font-size:.8rem; }
        .list-pagination-summary strong { color:#34465c; font-weight:600; }
        .list-pagination .pagination { gap:4px; flex-wrap:wrap; }
        .list-pagination .page-link { display:flex; align-items:center; justify-content:center; min-width:34px; height:34px; padding:5px 9px; margin:0; border:1px solid #e2e8f0; border-radius:7px !important; color:#385675; font-size:.8rem; box-shadow:none; }
        .list-pagination a.page-link:hover { background:#edf4fc; border-color:#adc7e7; }
        .list-pagination .active .page-link { background:var(--primary); border-color:var(--primary); color:#fff; }
        .list-pagination .disabled .page-link { background:#f8fafc; color:#a8b3c1; }
        .list-pagination a.page-link:focus-visible { outline:3px solid #90b8ea; outline-offset:2px; }
        .list-pagination .paging-mobile { display:none; }
        @media(max-width:700px) {
            .sidebar { width:70px; padding:12px 6px; }
            .sidebar .brand-text, .sidebar .link-label { display:none; }
            .sidebar .brand { justify-content:center; }
            .sidebar .nav-link { justify-content:center; padding:10px; }
            .main-content, .main-content.collapsed { margin-left:70px; padding:12px; }
            .list-pagination { flex-direction:column; align-items:center; }
            .list-pagination .paging-number { display:none; }
            .list-pagination .paging-mobile { display:list-item; }
            .list-pagination .page-link { min-width:36px; height:36px; }
        }

        .plan-row { cursor: pointer; }
        .plan-row:hover { background-color: #f8fafc; }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Perkecil/perbesar menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="brand">
            <i class="bi bi-newspaper"></i> <span class="brand-text">Pendataan Advertorial</span>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> <span class="link-label">Dashboard</span>
            </a>
            <a class="nav-link {{ request()->routeIs('dashboard.two') ? 'active' : '' }}" href="{{ route('dashboard.two') }}" title="Dashboard 2">
                <i class="bi bi-stars"></i> <span class="link-label">Dashboard 2</span>
            </a>
            <a class="nav-link {{ request()->routeIs('advertorials.index') ? 'active' : '' }}" href="{{ route('advertorials.index') }}">
                <i class="bi bi-grid"></i> <span class="link-label">Semua Data</span>
            </a>
            <a class="nav-link {{ request()->routeIs('advertorials.media') ? 'active' : '' }}" href="{{ route('advertorials.media') }}">
                <i class="bi bi-newspaper"></i> <span class="link-label">Media Cetak &amp; Online</span>
            </a>
            <a class="nav-link {{ request()->routeIs('advertorials.create') ? 'active' : '' }}" href="{{ route('advertorials.create') }}">
                <i class="bi bi-plus-circle"></i> <span class="link-label">Tambah Data</span>
            </a>
            <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                <i class="bi bi-building"></i> <span class="link-label">Kelola Perusahaan</span>
            </a>
            <a class="nav-link {{ request()->routeIs('media.*') ? 'active' : '' }}" href="{{ route('media.index') }}">
                <i class="bi bi-collection"></i> <span class="link-label">Kelola Media</span>
            </a>
            <a class="nav-link {{ request()->routeIs('plans.*') ? 'active' : '' }}" href="{{ route('plans.index') }}">
                <i class="bi bi-clipboard-data"></i> <span class="link-label">Planning Iklan</span>
            </a>
        </nav>
    </div>

    <div class="main-content" id="mainContent">
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('sidebarToggleBtn');

        function applyCollapsedState(isCollapsed) {
            sidebar.classList.toggle('collapsed', isCollapsed);
            mainContent.classList.toggle('collapsed', isCollapsed);
        }

        applyCollapsedState(localStorage.getItem('sidebarCollapsed') === 'true');

        toggleBtn.addEventListener('click', function () {
            const nowCollapsed = !sidebar.classList.contains('collapsed');
            applyCollapsedState(nowCollapsed);
            localStorage.setItem('sidebarCollapsed', nowCollapsed);
        });
    </script>
    <script src="{{ asset('js/dashboard-export.js') }}"></script>
    @stack('scripts')
</body>
</html>