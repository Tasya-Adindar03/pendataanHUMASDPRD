<style>
    .sidebar-user {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 1rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-user-name {
        color: #fff;
        font-weight: 600;
        font-size: 0.88rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sidebar.collapsed .sidebar-user { padding: 1rem 0.6rem; }
    .sidebar.collapsed .sidebar-user-name { justify-content: center; }
</style>

<div class="sidebar-user">
    <div class="sidebar-user-name">
        <i class="bi bi-person-circle"></i>
        <span class="link-label">
            {{ auth()->user()->name }}
            <small class="d-block fw-normal" style="opacity: .7;">{{ auth()->user()->isAdmin() ? 'Admin' : 'Staff' }}</small>
        </span>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start" title="Keluar">
            <i class="bi bi-box-arrow-right"></i> <span class="link-label">Logout</span>
        </button>
    </form>
</div>