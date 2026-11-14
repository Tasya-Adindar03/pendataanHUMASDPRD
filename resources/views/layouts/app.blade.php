<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pendataan Advertorial')</title>

    {{-- Pasang tema lebih awal supaya layar tidak berkedip --}}
    <script>
        (function () {
            var mode = 'auto';
            try { mode = localStorage.getItem('theme') || 'auto'; } catch (e) {}
            var dark = mode === 'dark' || (mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e3a5f;
            --primary-light: #2c5282;
            --accent: #3182ce;
            --sidebar-bg: #1e3a5f;
            --bg: #f4f6f9;
            --card-bg: #ffffff;
            --surface-2: #f8fafc;
            --input-bg: #ffffff;
            --text: #1a202c;
            --muted: #718096;
            --line: #e2e8f0;
            --line-soft: #edf2f7;
            --success: #38a169;
            --warning: #d69e2e;
            --danger: #e53e3e;
            --ok-bg: #def7ec;
            --warn-bg: #fef3c7;
            --bad-bg: #fde2e1;
            --warn-text: #b45309;
            --overdue-bg: #fff6f6;
            --overdue-hover: #ffecec;
            --sidebar-w: 68px;
            --sidebar-open: 232px;
        }

        [data-bs-theme="dark"] {
            --primary: #3b6fb0;
            --primary-light: #4a80c4;
            --accent: #5b9bd5;
            --sidebar-bg: #16284a;
            --bg: #0e1626;
            --card-bg: #17213a;
            --surface-2: #1e2a45;
            --input-bg: #111b30;
            --text: #e6eaf2;
            --muted: #94a3b8;
            --line: #2b3a57;
            --line-soft: #25324d;
            --ok-bg: rgba(56, 161, 105, .2);
            --warn-bg: rgba(214, 158, 46, .2);
            --bad-bg: rgba(229, 62, 62, .2);
            --warn-text: #f0b429;
            --overdue-bg: rgba(229, 62, 62, .08);
            --overdue-hover: rgba(229, 62, 62, .15);
        }

        * { font-family: 'Inter', sans-serif; }
        html, body { overflow-x: hidden; }
        body { background-color: var(--bg); color: var(--text); }

        /* Transisi halus saat tema berganti */
        html.theme-anim *, html.theme-anim *::before, html.theme-anim *::after {
            transition: background-color .35s ease, color .35s ease, border-color .35s ease, box-shadow .35s ease !important;
        }

        /* ===== Animasi dasar ===== */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes pop { from { transform: scale(0); } to { transform: scale(1); } }
        @keyframes grow { from { transform: scaleX(0); } to { transform: scaleX(1); } }
        @keyframes ripple { to { transform: scale(4); opacity: 0; } }
        @keyframes spinOnce { from { transform: rotate(-90deg) scale(.4); opacity: 0; } to { transform: none; opacity: 1; } }
        @keyframes ring {
            0%, 100% { transform: rotate(0); } 10% { transform: rotate(14deg); } 20% { transform: rotate(-12deg); }
            30% { transform: rotate(9deg); } 40% { transform: rotate(-6deg); } 50% { transform: rotate(0); }
        }

        /* ===== Sidebar melayang ===== */
        .sidebar {
            position: fixed; top: 20px; left: 20px; bottom: 20px;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            border-radius: 36px;
            padding: 18px 10px;
            display: flex; flex-direction: column;
            overflow: hidden;
            transition: width .28s cubic-bezier(.4, 0, .2, 1), box-shadow .28s;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(15, 30, 55, .25);
        }
        .sidebar:hover { width: var(--sidebar-open); box-shadow: 0 18px 44px rgba(15, 30, 55, .35); }

        .side-brand { display: flex; align-items: center; gap: 12px; padding: 0 6px; margin-bottom: 14px; color: #fff; text-decoration: none; font-weight: 700; font-size: .95rem; white-space: nowrap; }
        .side-logo { width: 36px; height: 36px; border-radius: 50%; background: #fff; color: #1e3a5f; display: grid; place-items: center; flex-shrink: 0; }

        .side-nav { flex: 1; overflow-y: auto; overflow-x: hidden; scrollbar-width: none; }
        .side-nav::-webkit-scrollbar { display: none; }

        .side-group {
            font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.45);
            white-space: nowrap; opacity: 0; max-height: 0; overflow: hidden; margin: 0 0 0 14px;
            transition: opacity .2s, max-height .28s, margin .28s;
        }
        .sidebar:hover .side-group { opacity: 1; max-height: 24px; margin: 14px 0 6px 14px; }

        .side-link {
            display: flex; align-items: center; gap: 14px;
            padding: 12px 13px; margin-bottom: 6px;
            border-radius: 18px; border: 0; background: transparent;
            color: rgba(255,255,255,.75); text-decoration: none;
            font-weight: 500; font-size: .92rem; white-space: nowrap; width: 100%; text-align: left;
            transition: background .2s, color .2s, transform .15s;
        }
        .side-link i { font-size: 1.2rem; width: 22px; text-align: center; flex-shrink: 0; }
        .side-link:hover { background: rgba(255,255,255,.12); color: #fff; }
        .side-link:active { transform: scale(.96); }
        .side-link.active { background: #fff; color: #1e3a5f; box-shadow: 0 4px 12px rgba(0,0,0,.15); }

        .side-text { opacity: 0; max-width: 0; overflow: hidden; white-space: nowrap; transition: opacity .2s, max-width .28s; }
        .sidebar:hover .side-text { opacity: 1; max-width: 180px; }

        .side-user { border-top: 1px solid rgba(255,255,255,.12); padding-top: 12px; }
        .side-profile {
            display: flex; align-items: center; gap: 12px; padding: 6px 7px; margin-bottom: 6px;
            color: #fff; white-space: nowrap; text-decoration: none; border-radius: 18px; transition: background .2s;
        }
        .side-profile:hover, .side-profile.active { background: rgba(255,255,255,.14); color: #fff; }
        .side-avatar { width: 34px; height: 34px; border-radius: 50%; background: #3182ce; color: #fff; display: grid; place-items: center; font-weight: 700; flex-shrink: 0; object-fit: cover; }
        .side-profile small { display: block; opacity: .65; font-size: .72rem; }
        .side-profile strong { font-size: .86rem; }

        .side-backdrop { display: none; }

        /* ===== Konten & header ===== */
        .main-content { margin-left: calc(var(--sidebar-w) + 40px); padding: 1.75rem 2rem 2rem 1rem; }

        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
        .topbar-left { display: flex; align-items: center; gap: .75rem; min-width: 0; }
        .topbar h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
        .topbar-sub { margin: .15rem 0 0; color: var(--muted); font-size: .9rem; }
        .topbar-actions { display: flex; align-items: center; gap: .75rem; }
        .menu-btn { display: none; flex-shrink: 0; }

        .search-pill {
            display: flex; align-items: center; background: var(--card-bg); border-radius: 999px;
            padding: 5px 5px 5px 18px; width: 260px; box-shadow: 0 1px 3px rgba(0,0,0,.08);
            transition: width .3s ease, box-shadow .2s;
        }
        .search-pill:focus-within { width: 340px; box-shadow: 0 0 0 3px rgba(49,130,206,.25); }
        .search-pill input { border: 0; outline: 0; background: transparent; color: var(--text); flex: 1; min-width: 0; font-size: .88rem; }
        .search-pill button { width: 34px; height: 34px; border-radius: 50%; border: 0; background: var(--primary); color: #fff; display: grid; place-items: center; transition: transform .15s; flex-shrink: 0; }
        .search-pill button:hover { transform: scale(1.08); }

        .icon-btn {
            width: 46px; height: 46px; border-radius: 50%; border: 0; background: var(--card-bg); color: var(--text);
            display: grid; place-items: center; font-size: 1.1rem; position: relative; flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,.08); transition: transform .15s, box-shadow .2s;
        }
        .icon-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.14); }
        .icon-btn.ring i { animation: ring 2.4s ease 1s 2; transform-origin: top center; }
        .icon-badge {
            position: absolute; top: -2px; right: -2px; min-width: 20px; height: 20px; padding: 0 5px;
            border-radius: 10px; background: var(--danger); color: #fff; font-size: .68rem; font-weight: 700;
            display: grid; place-items: center; border: 2px solid var(--bg);
        }

        .theme-toggle i { display: inline-block; }
        .theme-toggle .sun { display: none; color: #f6c453; }
        [data-bs-theme="dark"] .theme-toggle .moon { display: none; }
        [data-bs-theme="dark"] .theme-toggle .sun { display: inline-block; }
        .theme-toggle.spin i { animation: spinOnce .5s ease; }

        .notif-menu { width: min(340px, calc(100vw - 2rem)); border: 0; background: var(--card-bg); border-radius: 18px; box-shadow: 0 16px 40px rgba(0,0,0,.25); overflow: hidden; margin-top: 10px !important; }
        .dropdown-menu.show { animation: fadeIn .15s ease; }
        .notif-head { padding: 14px 16px; font-weight: 700; border-bottom: 1px solid var(--line-soft); }
        .notif-row { display: flex; gap: 12px; align-items: center; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid var(--line-soft); transition: background .15s; }
        .notif-row:hover { background: var(--surface-2); color: inherit; }
        .notif-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--danger); flex-shrink: 0; }
        .notif-foot { display: block; text-align: center; padding: 12px; font-weight: 600; font-size: .85rem; text-decoration: none; }

        /* ===== Kartu, tabel, badge ===== */
        .card-custom {
            background: var(--card-bg); border: none; border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08); padding: 1.5rem;
            animation: fadeUp .5s cubic-bezier(.2,.8,.2,1) both;
        }
        .row > *:nth-child(2) .card-custom { animation-delay: .06s; }
        .row > *:nth-child(3) .card-custom { animation-delay: .12s; }
        .row > *:nth-child(4) .card-custom { animation-delay: .18s; }

        .table-custom { --bs-table-bg: transparent; }
        .table-custom thead { background: var(--surface-2); }
        .table-custom thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; color: var(--muted); font-weight: 600; border-bottom: 2px solid var(--line); white-space: nowrap; }
        .table-custom tbody td { font-size: .9rem; vertical-align: middle; border-color: var(--line-soft); }
        .table-custom tbody tr { transition: background .15s; }
        .table-custom tbody tr:hover { background: var(--surface-2); }
        .table-custom tbody tr.row-overdue { background: var(--overdue-bg); }
        .table-custom tbody tr.row-overdue:hover { background: var(--overdue-hover); }

        .badge-status { padding: .4em .8em; border-radius: 20px; font-size: .75rem; font-weight: 600; white-space: nowrap; }
        .badge-sudah { background: var(--ok-bg); color: var(--success); }
        .badge-belum { background: var(--warn-bg); color: var(--warn-text); }
        .badge-jatuh { background: var(--bad-bg); color: var(--danger); }

        [data-bs-theme="dark"] [style*="#b45309"] { color: var(--warn-text) !important; }
        [data-bs-theme="dark"] .badge.bg-light { background-color: var(--surface-2) !important; color: var(--text) !important; border-color: var(--line) !important; }
        [data-bs-theme="dark"] .btn-light { background: var(--surface-2); border-color: var(--line); color: var(--text); }

        .progress { background: var(--line-soft); border-radius: 999px; }
        .progress-bar { transform-origin: left; animation: grow .9s cubic-bezier(.2,.8,.2,1) both; border-radius: 999px; }

        /* ===== Tombol & form ===== */
        .btn { border-radius: 12px; position: relative; overflow: hidden; transition: transform .12s ease, box-shadow .2s, background-color .2s; }
        .btn:active { transform: scale(.96); }
        .btn-primary-custom { background: var(--primary); color: #fff; border: none; border-radius: 999px; font-weight: 500; padding: .55rem 1.2rem; }
        .btn-primary-custom:hover { background: var(--primary-light); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 14px rgba(30,58,95,.3); }
        .ripple { position: absolute; border-radius: 50%; background: currentColor; opacity: .28; transform: scale(0); animation: ripple .6s ease-out; pointer-events: none; }

        .nav-pills .nav-link { color: var(--muted); font-weight: 600; font-size: .9rem; border-radius: 999px; transition: background .2s, color .2s; }
        .nav-pills .nav-link.active { background: var(--primary); color: #fff; }

        .form-label { font-weight: 600; font-size: .87rem; color: var(--text); }
        .form-control, .form-select { border-radius: 12px; border: 1px solid var(--line); font-size: .9rem; background-color: var(--input-bg); color: var(--text); }
        .form-control:focus, .form-select:focus { border-color: var(--accent); background-color: var(--input-bg); color: var(--text); box-shadow: 0 0 0 3px rgba(49,130,206,.18); }
        .form-control:disabled, .form-select:disabled { background-color: var(--surface-2); color: var(--muted); }
        .input-group-text { background: var(--surface-2); border-color: var(--line); color: var(--muted); }

        .kpi-card-link { text-decoration: none; color: inherit; display: block; transition: transform .18s ease, box-shadow .18s ease; border-radius: 20px; }
        .kpi-card-link:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,.15); color: inherit; }

        .pagination { flex-wrap: wrap; gap: 2px; }

        /* ===== Toast ===== */
        .toast-stack { position: fixed; top: 20px; right: 20px; z-index: 2000; display: flex; flex-direction: column; gap: 12px; pointer-events: none; }
        .toast-item {
            pointer-events: auto; min-width: 300px; max-width: 380px; background: var(--card-bg); color: var(--text); border-radius: 16px;
            box-shadow: 0 12px 32px rgba(0,0,0,.25); padding: 14px 16px 18px; display: flex; gap: 12px; align-items: flex-start;
            position: relative; overflow: hidden; animation: toastIn .5s cubic-bezier(.2,1.2,.3,1) both;
        }
        .toast-item.hide { animation: toastOut .3s ease forwards; }
        .toast-icon { width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; font-size: 1.15rem; flex-shrink: 0; animation: pop .5s .15s cubic-bezier(.2,1.6,.4,1) both; }
        .toast-success .toast-icon { background: var(--ok-bg); color: var(--success); }
        .toast-error .toast-icon { background: var(--bad-bg); color: var(--danger); }
        .toast-title { font-weight: 700; font-size: .9rem; }
        .toast-msg { font-size: .84rem; color: var(--muted); }
        .toast-close { border: 0; background: transparent; color: var(--muted); padding: 2px; line-height: 1; }
        .toast-bar { position: absolute; left: 0; bottom: 0; height: 4px; width: 100%; transform-origin: left; animation: toastBar 4.5s linear forwards; }
        .toast-success .toast-bar { background: var(--success); }
        .toast-error .toast-bar { background: var(--danger); }
        .toast-item:hover .toast-bar { animation-play-state: paused; }
        @keyframes toastIn { from { opacity: 0; transform: translateX(40px) scale(.95); } to { opacity: 1; transform: none; } }
        @keyframes toastOut { to { opacity: 0; transform: translateX(40px); } }
        @keyframes toastBar { from { transform: scaleX(1); } to { transform: scaleX(0); } }

        /* ===== Jendela konfirmasi ===== */
        .confirm-card { border: 0; background: var(--card-bg); border-radius: 24px; padding: 1.75rem 1.5rem; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
        .confirm-icon { width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 50%; background: var(--bad-bg); color: var(--danger); display: grid; place-items: center; font-size: 1.6rem; }
        .modal.show .confirm-icon { animation: pop .5s .1s cubic-bezier(.2,1.6,.4,1) both; }
        .modal-backdrop.show { opacity: .45; backdrop-filter: blur(2px); }

        /* ================= TAMPILAN TABLET & HP ================= */

        /* Sidebar menjadi menu geser */
        @media (max-width: 991.98px) {
            .sidebar {
                top: 0; left: 0; bottom: 0; width: 272px;
                border-radius: 0 28px 28px 0;
                transform: translateX(-105%);
                transition: transform .3s cubic-bezier(.4, 0, .2, 1), box-shadow .3s;
                box-shadow: none;
            }
            .sidebar:hover { width: 272px; box-shadow: none; }
            .sidebar.open { transform: none; box-shadow: 0 0 50px rgba(0,0,0,.45); }
            .sidebar .side-text { opacity: 1; max-width: 190px; }
            .sidebar .side-group { opacity: 1; max-height: 24px; margin: 14px 0 6px 14px; }

            .side-backdrop { position: fixed; inset: 0; background: rgba(8, 15, 30, .55); backdrop-filter: blur(2px); z-index: 999; animation: fadeIn .2s ease; }
            .side-backdrop.show { display: block; }

            .menu-btn { display: grid; }
            .main-content { margin-left: 0; padding: 1.25rem 1rem 2rem; }
        }

        /* HP */
        @media (max-width: 767.98px) {
            .main-content { padding: 1rem .85rem 1.75rem; }

            .topbar { gap: .75rem; margin-bottom: 1rem; }
            .topbar-left, .topbar-actions { flex: 1 1 100%; }
            .topbar h1 { font-size: 1.25rem; }
            .topbar-sub { font-size: .8rem; }
            .topbar-actions { gap: .5rem; }
            .search-pill, .search-pill:focus-within { flex: 1; width: auto; min-width: 0; }
            .icon-btn { width: 42px; height: 42px; }

            .card-custom { padding: 1.1rem; border-radius: 16px; }
            .card-custom .d-flex.justify-content-between { flex-wrap: wrap; gap: .5rem; }
            .card-custom > .d-flex > form.d-flex { width: 100%; }

            .nav-pills .nav-link { padding: .4rem .75rem; font-size: .82rem; }

            .toast-stack { left: 12px; right: 12px; top: 12px; }
            .toast-item { min-width: 0; max-width: none; }

            /* Tabel berubah menjadi kartu */
            .table-stack { border-collapse: separate; }
            .table-stack thead { display: none; }
            .table-stack tbody, .table-stack tr, .table-stack td { display: block; width: 100%; }
            .table-stack tr {
                background: var(--card-bg); border: 1px solid var(--line); border-radius: 16px;
                padding: .4rem .9rem; margin-bottom: .85rem;
            }
            .table-stack tr.row-overdue { background: var(--overdue-bg); }
            .table-stack td {
                display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;
                padding: .55rem 0; border: 0; border-bottom: 1px dashed var(--line-soft);
                text-align: right; white-space: normal !important; min-width: 0 !important;
            }
            .table-stack td:last-child { border-bottom: 0; justify-content: flex-end; }
            .table-stack td::before {
                content: attr(data-label); flex-shrink: 0; padding-top: 2px; text-align: left;
                font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--muted);
            }
            .table-stack td:not([data-label])::before, .table-stack td:last-child::before { display: none; }
            .table-stack td[colspan] { display: block; text-align: center; }
            .table-stack .cell { min-width: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>

@php
    $me = auth()->user();

    $overdueCount = \App\Models\Advertorial::where('status', 'jatuh_tempo')->count();
    $overdueList = \App\Models\Advertorial::with(['company', 'media'])
        ->where('status', 'jatuh_tempo')
        ->orderBy('tanggal_jatuh_tempo')
        ->limit(5)
        ->get();

    $subtitles = [
        'dashboard' => 'Ringkasan pembayaran advertorial dan kuota iklan',
        'advertorials.index' => 'Daftar tayangan beserta status pembayarannya',
        'advertorials.create' => 'Isi data tayangan dan pembayaran',
        'advertorials.edit' => 'Perbarui data tayangan dan pembayaran',
        'companies.index' => 'Perusahaan klien yang beriklan',
        'companies.create' => 'Tambahkan perusahaan klien baru',
        'companies.edit' => 'Perbarui data perusahaan klien',
        'media.index' => 'Atur media dan kuota tayang per bulan',
        'media.create' => 'Tambahkan media baru',
        'media.edit' => 'Perbarui data media',
        'media.show' => 'Atur kuota tayang per perusahaan dan bulan',
        'users.index' => 'Atur akun dan peran pengguna',
        'users.create' => 'Tambahkan akun pengguna baru',
        'users.edit' => 'Perbarui akun pengguna',
        'profile.edit' => 'Kelola foto, data diri, keamanan, dan tampilan',
    ];
    $currentSubtitle = $subtitles[optional(request()->route())->getName()] ?? null;
@endphp

    <div class="side-backdrop" id="sideBackdrop"></div>

    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="side-brand">
            <span class="side-logo"><i class="bi bi-newspaper"></i></span>
            <span class="side-text">Pendataan Advertorial</span>
        </a>

        <nav class="side-nav">
            <div class="side-group">Data</div>

            <a class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Dashboard">
                <i class="bi bi-speedometer2"></i><span class="side-text">Dashboard</span>
            </a>
            <a class="side-link {{ request()->routeIs('advertorials.*') ? 'active' : '' }}" href="{{ route('advertorials.index') }}" title="Media Cetak & Online">
                <i class="bi bi-newspaper"></i><span class="side-text">Media Cetak &amp; Online</span>
            </a>

            <div class="side-group">Pengaturan</div>

            <a class="side-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}" title="Kelola Perusahaan">
                <i class="bi bi-building"></i><span class="side-text">Kelola Perusahaan</span>
            </a>
            <a class="side-link {{ request()->routeIs('media.*') ? 'active' : '' }}" href="{{ route('media.index') }}" title="Kelola Media & Kuota">
                <i class="bi bi-collection"></i><span class="side-text">Kelola Media &amp; Kuota</span>
            </a>

            @if ($me->isAdmin())
                <a class="side-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" title="Kelola User">
                    <i class="bi bi-people"></i><span class="side-text">Kelola User</span>
                </a>
            @endif
        </nav>

        <div class="side-user">
            <a href="{{ route('profile.edit') }}" class="side-profile {{ request()->routeIs('profile.*') ? 'active' : '' }}" title="Edit profil">
                @if ($me->foto_url)
                    <img src="{{ $me->foto_url }}" alt="" class="side-avatar">
                @else
                    <span class="side-avatar">{{ $me->inisial }}</span>
                @endif
                <span class="side-text">
                    <strong>{{ $me->name }}</strong>
                    <small>{{ $me->isAdmin() ? 'Admin' : 'Staff' }} &middot; Edit profil</small>
                </span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="side-link" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i><span class="side-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content" id="mainContent">

        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="icon-btn menu-btn" id="menuToggle" aria-label="Buka menu">
                    <i class="bi bi-list"></i>
                </button>

                <div style="min-width: 0;">
                    <h1>
                        @hasSection('heading')
                            @yield('heading')
                        @else
                            @yield('title', 'Dashboard')
                        @endif
                    </h1>

                    @hasSection('subtitle')
                        <p class="topbar-sub">@yield('subtitle')</p>
                    @elseif ($currentSubtitle)
                        <p class="topbar-sub">{{ $currentSubtitle }}</p>
                    @endif
                </div>
            </div>

            <div class="topbar-actions">
                <form action="{{ route('advertorials.index') }}" method="GET" class="search-pill">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul tayangan..." autocomplete="off">
                    <button type="submit" aria-label="Cari"><i class="bi bi-search"></i></button>
                </form>

                <button type="button" class="icon-btn theme-toggle" id="themeToggle" title="Ganti tampilan terang / gelap" aria-label="Ganti tema">
                    <i class="bi bi-moon-stars moon"></i>
                    <i class="bi bi-sun sun"></i>
                </button>

                <div class="dropdown">
                    <button class="icon-btn {{ $overdueCount > 0 ? 'ring' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi">
                        <i class="bi bi-bell"></i>
                        @if ($overdueCount > 0)
                            <span class="icon-badge">{{ $overdueCount > 9 ? '9+' : $overdueCount }}</span>
                        @endif
                    </button>

                    <div class="dropdown-menu dropdown-menu-end notif-menu p-0">
                        <div class="notif-head">Tagihan Terlambat</div>

                        @forelse ($overdueList as $o)
                            <a class="notif-row" href="{{ route('advertorials.index', ['status' => 'jatuh_tempo', 'company_id' => $o->company_id, 'media_id' => $o->media_id]) }}">
                                <span class="notif-dot"></span>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="fw-semibold small text-truncate">{{ $o->company->nama_perusahaan ?? '-' }} &middot; {{ $o->media->nama_media ?? '-' }}</div>
                                    <div class="small text-muted text-truncate">{{ $o->judul }}</div>
                                </div>
                                <div class="small text-danger fw-semibold text-nowrap">{{ abs($o->umur_piutang ?? 0) }} hari</div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted small">
                                <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                                Semua tagihan aman
                            </div>
                        @endforelse

                        @if ($overdueCount > 0)
                            <a class="notif-foot" href="{{ route('advertorials.index', ['status' => 'jatuh_tempo']) }}">Lihat semua ({{ $overdueCount }})</a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        @yield('content')
    </div>

    {{-- Tempat notifikasi --}}
    <div class="toast-stack" id="toastStack"></div>

    {{-- Jendela konfirmasi hapus --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content confirm-card text-center">
                <div class="confirm-icon"><i class="bi bi-trash3"></i></div>
                <h6 class="fw-bold mb-1">Konfirmasi</h6>
                <p class="text-muted small mb-4" id="confirmText">Yakin?</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger w-100" id="confirmYes">Ya, hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== Menu geser di HP =====
        (function () {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sideBackdrop');
            const btn = document.getElementById('menuToggle');

            function setOpen(open) {
                sidebar.classList.toggle('open', open);
                backdrop.classList.toggle('show', open);
                document.body.style.overflow = open ? 'hidden' : '';
            }

            btn.addEventListener('click', function () { setOpen(!sidebar.classList.contains('open')); });
            backdrop.addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
            sidebar.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });
            window.matchMedia('(min-width: 992px)').addEventListener('change', function (e) { if (e.matches) setOpen(false); });
        })();

        // ===== Tema terang / gelap =====
        (function () {
            const root = document.documentElement;
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const getMode = () => { try { return localStorage.getItem('theme') || 'auto'; } catch (e) { return 'auto'; } };
            const resolve = (mode) => (mode === 'dark' || (mode === 'auto' && media.matches)) ? 'dark' : 'light';

            function apply() {
                root.setAttribute('data-bs-theme', resolve(getMode()));
                document.dispatchEvent(new CustomEvent('themechange'));
            }

            window.setThemeMode = function (mode) {
                root.classList.add('theme-anim');
                try { localStorage.setItem('theme', mode); } catch (e) {}
                apply();
                setTimeout(function () { root.classList.remove('theme-anim'); }, 450);
            };

            media.addEventListener('change', function () { if (getMode() === 'auto') apply(); });

            const toggle = document.getElementById('themeToggle');
            toggle.addEventListener('click', function () {
                toggle.classList.remove('spin');
                void toggle.offsetWidth;
                toggle.classList.add('spin');
                setThemeMode(root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
            });
        })();

        // ===== Notifikasi toast =====
        function showToast(type, message) {
            const stack = document.getElementById('toastStack');
            const el = document.createElement('div');
            el.className = 'toast-item toast-' + type;
            el.innerHTML =
                '<div class="toast-icon"><i class="bi ' + (type === 'success' ? 'bi-check-lg' : 'bi-exclamation-lg') + '"></i></div>' +
                '<div class="flex-grow-1"><div class="toast-title"></div><div class="toast-msg"></div></div>' +
                '<button type="button" class="toast-close" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>' +
                '<div class="toast-bar"></div>';
            el.querySelector('.toast-title').textContent = type === 'success' ? 'Berhasil' : 'Perhatian';
            el.querySelector('.toast-msg').textContent = message;

            const close = () => { el.classList.add('hide'); setTimeout(() => el.remove(), 300); };
            el.querySelector('.toast-close').addEventListener('click', close);
            el.querySelector('.toast-bar').addEventListener('animationend', close);
            stack.appendChild(el);
        }

        @if (session('success'))
            showToast('success', @json(session('success')));
        @endif
        @if (session('error'))
            showToast('error', @json(session('error')));
        @endif

        // ===== Konfirmasi hapus (mengubah confirm() bawaan menjadi jendela kecil) =====
        document.querySelectorAll('form[onsubmit*="confirm("]').forEach(function (form) {
            const m = form.getAttribute('onsubmit').match(/confirm\('([^']*)'\)/);
            form.removeAttribute('onsubmit');
            form.dataset.confirm = m ? m[1] : 'Yakin ingin melanjutkan?';
        });

        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        let pendingForm = null;

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form.dataset || !form.dataset.confirm || form.dataset.confirmed === '1') return;
            e.preventDefault();
            pendingForm = form;
            document.getElementById('confirmText').textContent = form.dataset.confirm;
            confirmModal.show();
        }, true);

        document.getElementById('confirmYes').addEventListener('click', function () {
            if (!pendingForm) return;
            pendingForm.dataset.confirmed = '1';
            confirmModal.hide();
            pendingForm.submit();
        });

        // ===== Tombol berubah jadi spinner saat form dikirim (mencegah data tersimpan dobel) =====
        document.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            const form = e.target;
            if ((form.method || '').toLowerCase() !== 'post') return;

            const btn = e.submitter || form.querySelector('button[type="submit"]');
            if (!btn) return;

            setTimeout(function () {
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>' +
                    (btn.classList.contains('btn-sm') || btn.classList.contains('side-link') ? '' : ' Memproses...');
                btn.disabled = true;
            }, 0);
        });

        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            document.querySelectorAll('button[data-original-html]').forEach(function (b) {
                b.innerHTML = b.dataset.originalHtml;
                b.disabled = false;
            });
        });

        // ===== Efek riak saat tombol diklik =====
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn');
            if (!btn || btn.disabled) return;
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const r = document.createElement('span');
            r.className = 'ripple';
            r.style.width = r.style.height = size + 'px';
            r.style.left = (e.clientX - rect.left - size / 2) + 'px';
            r.style.top = (e.clientY - rect.top - size / 2) + 'px';
            btn.appendChild(r);
            setTimeout(function () { r.remove(); }, 600);
        });

        // ===== Angka menghitung naik =====
        document.querySelectorAll('[data-count]').forEach(function (el) {
            const target = parseFloat(el.dataset.count) || 0;
            const prefix = el.dataset.prefix || '';
            const duration = 900;
            const start = performance.now();

            function tick(now) {
                const p = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = prefix + Math.round(target * eased).toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        });
    </script>
    @stack('scripts')
</body>
</html>