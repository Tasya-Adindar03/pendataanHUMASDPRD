@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')

<style>
    .avatar-xl {
        width: 128px; height: 128px; border-radius: 50%; margin: 0 auto 1rem; overflow: hidden;
        background: var(--primary); color: #fff; font-size: 3rem; font-weight: 700;
        display: grid; place-items: center; box-shadow: 0 8px 24px rgba(0,0,0,.2);
        cursor: pointer; transition: transform .2s;
    }
    .avatar-xl:hover { transform: scale(1.04); }
    .avatar-xl img { width: 100%; height: 100%; object-fit: cover; }

    .meta-row { display: flex; justify-content: space-between; padding: .55rem 0; border-top: 1px solid var(--line-soft); font-size: .85rem; }
    .meta-row span:first-child { color: var(--muted); }

    .strength-track { height: 6px; background: var(--line-soft); border-radius: 999px; overflow: hidden; }
    #strengthBar { height: 100%; width: 0; border-radius: 999px; transition: width .3s, background .3s; }

    .theme-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; }
    .theme-option {
        border: 2px solid var(--line); background: var(--surface-2); color: var(--text);
        border-radius: 16px; padding: 1rem .5rem; font-size: .85rem; font-weight: 600;
        display: flex; flex-direction: column; align-items: center; gap: .4rem;
        transition: border-color .2s, transform .15s, background .2s;
    }
    .theme-option i { font-size: 1.5rem; }
    .theme-option:hover { transform: translateY(-3px); border-color: var(--accent); }
    .theme-option.active { border-color: var(--accent); background: var(--card-bg); box-shadow: 0 0 0 3px rgba(49,130,206,.2); }
</style>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Bagian 1: foto + nama + email (satu form) --}}
<form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card-custom h-100 text-center">
                <div class="avatar-xl" id="avatarBox" onclick="document.getElementById('foto').click()" title="Klik untuk ganti foto">
                    @if ($user->foto_url)
                        <img src="{{ $user->foto_url }}" alt="Foto profil">
                    @else
                        <span>{{ $user->inisial }}</span>
                    @endif
                </div>

                <input type="file" name="foto" id="foto" accept="image/png,image/jpeg,image/webp" class="d-none">

                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <span class="badge {{ $user->isAdmin() ? 'bg-primary' : 'bg-secondary' }} mb-3">{{ $user->isAdmin() ? 'Admin' : 'Staff' }}</span>

                <div class="d-flex justify-content-center gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('foto').click()">
                        <i class="bi bi-camera"></i> {{ $user->foto ? 'Ganti Foto' : 'Pasang Foto' }}
                    </button>
                    @if ($user->foto)
                        <button type="submit" form="deletePhotoForm" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    @endif
                </div>

                <small class="text-muted d-block">JPG, PNG, atau WEBP &middot; maksimal 2 MB</small>
                <small class="text-success d-none mt-2" id="photoHint"><i class="bi bi-info-circle"></i> Foto baru belum tersimpan. Klik <strong>Simpan Perubahan</strong>.</small>

                <div class="mt-4 text-start">
                    <div class="meta-row"><span>Bergabung</span><span>{{ $user->created_at?->translatedFormat('d F Y') ?? '-' }}</span></div>
                    <div class="meta-row"><span>Terakhir diubah</span><span>{{ $user->updated_at?->diffForHumans() ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-custom h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-person-lines-fill"></i> Informasi Akun</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        <small class="text-muted">Dipakai untuk login</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary-custom"><i class="bi bi-check2"></i> Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Form hapus foto (di luar form utama, dihubungkan lewat atribut form=) --}}
<form id="deletePhotoForm" action="{{ route('profile.photo.destroy') }}" method="POST" class="d-none" onsubmit="return confirm('Hapus foto profil?')">
    @csrf
    @method('DELETE')
</form>

{{-- Bagian 2: password + tampilan --}}
@php $pwErrors = $errors->getBag('updatePassword'); @endphp

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock"></i> Ubah Password</h6>

            @if ($pwErrors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0">
                        @foreach ($pwErrors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Password Saat Ini</label>
                    <div class="input-group">
                        <input type="password" name="current_password" id="current_password" class="form-control" required autocomplete="current-password">
                        <button type="button" class="btn btn-outline-secondary" data-toggle-password="#current_password" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="password" id="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" data-toggle-password="#new_password" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="strength-track mt-2"><div id="strengthBar"></div></div>
                    <small id="strengthLabel" class="fw-semibold"></small>
                    <small class="text-muted d-block">Minimal 8 karakter. Campur huruf besar, kecil, angka, dan simbol agar lebih kuat.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Ulangi Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="confirm_password" class="form-control" required autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" data-toggle-password="#confirm_password" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom"><i class="bi bi-key"></i> Ubah Password</button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-custom h-100">
            <h6 class="fw-bold mb-1"><i class="bi bi-palette"></i> Tampilan</h6>
            <p class="text-muted small mb-3">Pilihan ini tersimpan di browser yang sedang kamu pakai.</p>

            <div class="theme-options">
                <button type="button" class="theme-option" data-mode="light"><i class="bi bi-sun"></i>Terang</button>
                <button type="button" class="theme-option" data-mode="dark"><i class="bi bi-moon-stars"></i>Gelap</button>
                <button type="button" class="theme-option" data-mode="auto"><i class="bi bi-circle-half"></i>Ikuti Perangkat</button>
            </div>

            <p class="text-muted small mt-3 mb-0">
                <i class="bi bi-lightbulb"></i> Tombol bulan/matahari di pojok kanan atas juga bisa dipakai untuk berganti dengan cepat.
            </p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Pratinjau foto sebelum disimpan
    document.getElementById('foto').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            showToast('error', 'Ukuran foto maksimal 2 MB.');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('avatarBox').innerHTML = '<img src="' + e.target.result + '" alt="Pratinjau foto">';
            document.getElementById('photoHint').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });

    // Tampilkan / sembunyikan password
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.querySelector(btn.dataset.togglePassword);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // Indikator kekuatan password
    const pw = document.getElementById('new_password');
    const bar = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    const levels = [
        { w: 0,   c: '#cbd5e0', t: '' },
        { w: 25,  c: '#e53e3e', t: 'Lemah' },
        { w: 50,  c: '#d69e2e', t: 'Cukup' },
        { w: 75,  c: '#3182ce', t: 'Bagus' },
        { w: 100, c: '#38a169', t: 'Kuat' }
    ];

    pw.addEventListener('input', function () {
        const v = pw.value;
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
        if (/\d/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;

        const l = v ? levels[Math.max(score, 1)] : levels[0];
        bar.style.width = l.w + '%';
        bar.style.background = l.c;
        label.textContent = l.t;
        label.style.color = l.c;
    });

    // Pilihan tema
    function markThemeOption() {
        let mode = 'auto';
        try { mode = localStorage.getItem('theme') || 'auto'; } catch (e) {}
        document.querySelectorAll('.theme-option').forEach(function (b) {
            b.classList.toggle('active', b.dataset.mode === mode);
        });
    }

    document.querySelectorAll('.theme-option').forEach(function (b) {
        b.addEventListener('click', function () { setThemeMode(b.dataset.mode); });
    });

    document.addEventListener('themechange', markThemeOption);
    markThemeOption();
</script>
@endpush