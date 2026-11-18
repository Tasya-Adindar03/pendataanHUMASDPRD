@extends('layouts.app')
@section('title', 'Kelola Kuota Planning')
@section('subtitle', 'Atur kuota tayang tiap perusahaan, per media dan bulan')

@section('content')

<style>
    .kuota-input { width: 110px; text-align: center; font-weight: 600; }
    .kuota-input.changed { border-color: var(--accent); background-color: rgba(49,130,206,.1); }
    .stat-tile { border-radius: 16px; background: var(--card-bg); padding: .85rem 1rem; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .stat-tile small { color: var(--muted); font-weight: 600; display: block; font-size: .78rem; }
    .stat-tile strong { font-size: 1.5rem; line-height: 1.3; }
    .save-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .dirty-info { font-size: .85rem; color: var(--muted); }
    .dirty-info.on { color: var(--accent); font-weight: 600; }
</style>

@if ($mediaList->isEmpty())
    <div class="card-custom text-center py-5">
        <i class="bi bi-collection text-muted" style="font-size: 2.5rem;"></i>
        <div class="text-muted mt-2">Belum ada media terdaftar.</div>
    </div>
@else

    {{-- Pilihan media dan periode --}}
    <div class="card-custom mb-3">
        <form method="GET" action="{{ route('kuota.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label">Media</label>
                <select name="media_id" class="form-select auto-submit">
                    @foreach ($mediaList->groupBy('tipe') as $tipe => $group)
                        <optgroup label="Media {{ ucfirst($tipe) }}">
                            @foreach ($group as $m)
                                <option value="{{ $m->id }}" @selected($m->id == $media->id)>{{ $m->nama_media }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="col-7 col-md-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select auto-submit">
                    @foreach (range(1, 12) as $n)
                        <option value="{{ $n }}" @selected($bulan === $n)>{{ \Carbon\Carbon::create(null, $n, 1)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-5 col-md-3">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-select auto-submit">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @selected($tahun === $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="row g-3 mb-3">
        <div class="col-4">
            <div class="stat-tile"><small>Total Kuota</small><strong>{{ $totalKuota }}</strong></div>
        </div>
        <div class="col-4">
            <div class="stat-tile"><small>Kuota Tersisa</small><strong class="text-success">{{ $totalSisa }}</strong></div>
        </div>
        <div class="col-4">
            <div class="stat-tile"><small>Realisasi</small><strong class="text-danger">{{ $totalTerpakai }}</strong></div>
        </div>
    </div>

    {{-- Daftar perusahaan + isian kuota --}}
    <form id="kuotaForm" action="{{ route('kuota.store') }}" method="POST">
        @csrf
        <input type="hidden" name="media_id" value="{{ $media->id }}">
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="card-custom">

            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="save-row mb-3">
                <div>
                    <h6 class="fw-bold mb-0">{{ $media->nama_media }}</h6>
                    <small class="text-muted">{{ \Carbon\Carbon::create(null, $bulan, 1)->translatedFormat('F') }} {{ $tahun }}</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="dirty-info">Belum ada perubahan</span>
                    <button type="submit" class="btn btn-primary-custom btn-sm save-btn" disabled>
                        <i class="bi bi-check2"></i> Simpan Kuota
                    </button>
                </div>
            </div>

            @if ($companies->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-building text-muted" style="font-size: 2.5rem;"></i>
                    <div class="text-muted mt-2 mb-3">Belum ada perusahaan.</div>
                    <a href="{{ route('companies.create') }}" class="btn btn-sm btn-primary-custom"><i class="bi bi-plus-lg"></i> Tambah Perusahaan</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-custom table-stack align-middle">
                        <thead>
                            <tr>
                                <th>Perusahaan</th>
                                <th>Kuota</th>
                                <th>Realisasi</th>
                                <th style="width: 180px;">Progress</th>
                                <th>Total Kuota</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($companies as $company)
                                @php
                                    $plan = $plans->get($company->id);
                                    $used = $plan ? ($terpakai[$plan->id] ?? 0) : 0;
                                    $sisa = $plan ? max($plan->kuota - $used, 0) : null;
                                    $persen = ($plan && $plan->kuota > 0) ? min(round($used / $plan->kuota * 100), 100) : 0;
                                @endphp
                                <tr>
                                    <td data-label="Perusahaan" class="fw-semibold"><div class="cell">{{ $company->nama_perusahaan }}</div></td>

                                    <td data-label="Kuota">
                                        <div class="cell">
                                            @if ($plan)
                                                <span class="text-success fw-bold">{{ $sisa }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td data-label="Realisasi">
                                        <div class="cell">
                                            @if ($plan)
                                                <span class="text-danger fw-bold">{{ $used }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td data-label="Progress">
                                        <div class="cell" style="min-width: 120px;">
                                            @if ($plan)
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar {{ $persen >= 100 ? 'bg-success' : '' }}"
                                                         style="width: {{ $persen }}%; background-color: {{ $persen >= 100 ? '' : '#3182ce' }};"></div>
                                                </div>
                                                <small class="text-muted">{{ $persen }}%</small>
                                            @else
                                                <small class="text-muted">Belum diatur</small>
                                            @endif
                                        </div>
                                    </td>

                                    <td data-label="Total Kuota">
                                        <div class="cell">
                                            <input type="number" min="0" max="100000" inputmode="numeric"
                                                   name="kuota[{{ $company->id }}]"
                                                   value="{{ old('kuota.' . $company->id, $plan?->kuota) }}"
                                                   data-original="{{ $plan?->kuota }}"
                                                   placeholder="0"
                                                   class="form-control form-control-sm kuota-input">
                                        </div>
                                    </td>

                                    <td data-label="Aksi">
                                        <div class="cell">
                                            @if ($plan)
                                                <a href="{{ route('advertorials.index', ['plan_id' => $plan->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat tayangan">
                                                    <i class="bi bi-list-ul"></i> <span class="d-md-none">Lihat tayangan</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="save-row mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Kuota (hijau) = yang masih tersisa, Realisasi (merah) = yang sudah terpakai.
                        Kosongkan atau isi 0 untuk menghapus kuota.
                    </small>
                    <button type="submit" class="btn btn-primary-custom btn-sm save-btn" disabled>
                        <i class="bi bi-check2"></i> Simpan Kuota
                    </button>
                </div>
            @endif
        </div>
    </form>
@endif

@endsection

@push('scripts')
<script>
    // Ganti media / bulan / tahun langsung memuat ulang halaman
    document.querySelectorAll('.auto-submit').forEach(function (s) {
        s.addEventListener('change', function () { s.form.submit(); });
    });

    (function () {
        const form = document.getElementById('kuotaForm');
        if (!form) return;

        const inputs = form.querySelectorAll('.kuota-input');
        const buttons = form.querySelectorAll('.save-btn');
        const infos = form.querySelectorAll('.dirty-info');
        const norm = (v) => (v === '' || v === null || isNaN(parseInt(v, 10))) ? '0' : String(parseInt(v, 10));
        let dirty = 0, submitting = false;

        function refresh() {
            dirty = 0;
            inputs.forEach(function (i) {
                const changed = norm(i.value) !== norm(i.dataset.original);
                i.classList.toggle('changed', changed);
                if (changed) dirty++;
            });

            buttons.forEach(function (b) { b.disabled = dirty === 0; });
            infos.forEach(function (el) {
                el.textContent = dirty ? dirty + ' perusahaan diubah, belum disimpan' : 'Belum ada perubahan';
                el.classList.toggle('on', dirty > 0);
            });
        }

        inputs.forEach(function (i) {
            i.addEventListener('input', refresh);
            i.addEventListener('focus', function () { i.select(); });
        });

        form.addEventListener('submit', function () { submitting = true; });

        // Peringatan bila meninggalkan halaman saat ada perubahan yang belum disimpan
        window.addEventListener('beforeunload', function (e) {
            if (dirty > 0 && !submitting) { e.preventDefault(); e.returnValue = ''; }
        });

        refresh();
    })();
</script>
@endpush