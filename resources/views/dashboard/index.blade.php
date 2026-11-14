@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Halo, ' . auth()->user()->name . '!')

@section('content')

    <style>
        /* ===== Pemilih periode ===== */
        .period-bar { display: flex; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.25rem; }
        .period-btn {
            display: flex; align-items: center; gap: .75rem; background: var(--card-bg); color: var(--text);
            border: 0; border-radius: 16px; padding: .55rem 1rem .55rem .6rem; text-align: left;
            box-shadow: 0 1px 3px rgba(0,0,0,.08); transition: transform .15s, box-shadow .2s;
        }
        .period-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.14); }
        .period-ico { width: 38px; height: 38px; border-radius: 12px; background: var(--surface-2); color: var(--accent); display: grid; place-items: center; font-size: 1.1rem; flex-shrink: 0; }
        .period-btn strong { display: block; font-size: .9rem; line-height: 1.2; }
        .period-btn small { color: var(--muted); font-size: .76rem; }
        .period-btn .bi-chevron-down { color: var(--muted); font-size: .8rem; margin-left: .25rem; }
        .period-note { color: var(--muted); font-size: .8rem; }

        .period-menu {
            border: 0; background: var(--card-bg); border-radius: 18px; overflow: hidden;
            box-shadow: 0 16px 40px rgba(0,0,0,.25); width: min(520px, calc(100vw - 2rem)); margin-top: 10px !important;
        }
        .period-grid { display: grid; grid-template-columns: 1fr 1fr; }
        .period-presets { padding: .6rem; border-right: 1px solid var(--line-soft); }
        .period-item {
            display: block; padding: .55rem .8rem; border-radius: 12px; color: var(--text);
            text-decoration: none; font-size: .88rem; font-weight: 500; transition: background .15s;
        }
        .period-item:hover { background: var(--surface-2); color: var(--text); }
        .period-item.active { background: var(--primary); color: #fff; }
        .period-custom { padding: 1rem; }

        /* ===== Kartu angka ===== */
        .kpi-tile { display: flex; align-items: center; gap: 14px; padding: 1.1rem 1.25rem; height: 100%; }
        .kpi-icon { width: 48px; height: 48px; border-radius: 50%; display: grid; place-items: center; font-size: 1.25rem; background: var(--surface-2); color: var(--primary); flex-shrink: 0; }
        .kpi-icon.green { background: var(--ok-bg); color: var(--success); }
        .kpi-body { min-width: 0; }
        .kpi-name { font-size: .8rem; color: var(--muted); font-weight: 600; }
        .kpi-num { font-size: 1.35rem; font-weight: 700; line-height: 1.25; word-break: break-word; }
        .kpi-sub { margin-top: 2px; display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; font-size: .75rem; }

        .kpi-highlight { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #fff; position: relative; overflow: hidden; }
        .kpi-highlight .kpi-name { color: rgba(255,255,255,.75); }
        .kpi-highlight .kpi-icon { background: rgba(255,255,255,.18); color: #fff; }
        .kpi-highlight small { color: rgba(255,255,255,.8); }
        .kpi-wave { position: absolute; right: 10px; bottom: 8px; width: 90px; opacity: .4; pointer-events: none; }

        .delta { display: inline-flex; align-items: center; font-size: .72rem; font-weight: 700; padding: 1px 8px 1px 4px; border-radius: 999px; }
        .delta i { font-size: 1rem; line-height: 1; }
        .delta-up { background: var(--ok-bg); color: var(--success); }
        .delta-down { background: var(--bad-bg); color: var(--danger); }
        .delta-flat { background: var(--surface-2); color: var(--muted); }
        .delta.on-dark { background: rgba(255,255,255,.2); color: #fff; }

        .chart-box { position: relative; height: 270px; }
        .chart-box.short { height: 230px; }

        .notif-item, .quota-item {
            display: block; padding: .75rem .6rem; border-bottom: 1px solid var(--line-soft);
            text-decoration: none; color: inherit; border-radius: 12px; transition: background .15s, transform .15s;
        }
        .notif-item { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
        .notif-item:last-child, .quota-item:last-child { border-bottom: none; }
        .notif-item:hover, .quota-item:hover { background: var(--surface-2); color: inherit; transform: translateX(3px); }
        .list-scroll { max-height: 330px; overflow-y: auto; }
        .empty-note { text-align: center; color: var(--muted); padding: 2rem 0; }
        .empty-note i { font-size: 1.8rem; display: block; margin-bottom: .4rem; color: var(--success); }

        @media (max-width: 575.98px) {
            .period-main { flex: 1; min-width: 0; }
            .period-btn { width: 100%; }
            .period-note { flex: 1 1 100%; }
            .period-grid { grid-template-columns: 1fr; }
            .period-presets { border-right: 0; border-bottom: 1px solid var(--line-soft); display: grid; grid-template-columns: 1fr 1fr; }

            .kpi-tile { padding: .85rem; gap: 10px; }
            .kpi-icon { width: 38px; height: 38px; font-size: 1.05rem; }
            .kpi-num { font-size: 1.02rem; }
            .kpi-name { font-size: .74rem; }
            .kpi-wave { display: none; }

            .chart-box { height: 220px; }
            .chart-box.short { height: 210px; }
        }
    </style>

    @php
        $deltaBadge = function ($pct, $invert = false, $dark = false) {
            if ($pct === 'baru') {
                $icon = 'bi-stars'; $text = 'Baru'; $cls = 'flat';
            } elseif ($pct > 0) {
                $icon = 'bi-arrow-up-short';
                $text = '+' . rtrim(rtrim(number_format($pct, 1, ',', '.'), '0'), ',') . '%';
                $cls = $invert ? 'down' : 'up';
            } elseif ($pct < 0) {
                $icon = 'bi-arrow-down-short';
                $text = rtrim(rtrim(number_format($pct, 1, ',', '.'), '0'), ',') . '%';
                $cls = $invert ? 'up' : 'down';
            } else {
                $icon = 'bi-dash'; $text = '0%'; $cls = 'flat';
            }

            return '<span class="delta delta-' . $cls . ($dark ? ' on-dark' : '') . '"><i class="bi ' . $icon . '"></i>' . $text . '</span>';
        };
    @endphp

    {{-- Pemilih periode --}}
    <div class="period-bar">
        <div class="dropdown period-main">
            <button type="button" class="period-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <span class="period-ico"><i class="bi bi-calendar3"></i></span>
                <span style="min-width: 0;">
                    <strong>{{ $periodeLabel }}</strong>
                    <small>{{ $rangeText }}</small>
                </span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </button>

            <div class="dropdown-menu period-menu p-0">
                <div class="period-grid">
                    <div class="period-presets">
                        @foreach ($presets as $key => $name)
                            <a href="{{ route('dashboard', ['periode' => $key]) }}" class="period-item {{ $periode === $key ? 'active' : '' }}">{{ $name }}</a>
                        @endforeach
                    </div>

                    <form action="{{ route('dashboard') }}" method="GET" class="period-custom">
                        <input type="hidden" name="periode" value="custom">
                        <div class="fw-bold small mb-3"><i class="bi bi-calendar-range"></i> Rentang kustom</div>

                        <label class="form-label small">Dari tanggal</label>
                        <input type="date" name="dari" class="form-control form-control-sm mb-2" value="{{ $dariInput }}" required>

                        <label class="form-label small">Sampai tanggal</label>
                        <input type="date" name="sampai" class="form-control form-control-sm" value="{{ $sampaiInput }}" required>

                        <button type="submit" class="btn btn-sm btn-primary-custom w-100 mt-3">Terapkan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="dropdown" style="padding: .62rem .9rem;">
                <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Unduh Laporan</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('export.pdf', $rangeParams) }}"><i class="bi bi-file-earmark-pdf text-danger"></i> PDF</a></li>
                <li><a class="dropdown-item" href="{{ route('export.excel', $rangeParams) }}"><i class="bi bi-file-earmark-excel text-success"></i> Excel</a></li>
            </ul>
        </div>

        @if ($prevText)
            <span class="period-note"><i class="bi bi-arrow-left-right"></i> Dibandingkan dengan {{ $prevText }}</span>
        @endif
    </div>

    {{-- Baris 1: kartu angka --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index', $rangeParams) }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile">
                    <span class="kpi-icon"><i class="bi bi-stack"></i></span>
                    <div class="kpi-body">
                        <div class="kpi-name">Total Data</div>
                        <div class="kpi-num" data-count="{{ $totalData }}">{{ $totalData }}</div>
                        @if ($delta)<div class="kpi-sub">{!! $deltaBadge($delta['data']) !!}</div>@endif
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index', $rangeParams) }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile">
                    <span class="kpi-icon"><i class="bi bi-cash-stack"></i></span>
                    <div class="kpi-body">
                        <div class="kpi-name">Total Nominal</div>
                        <div class="kpi-num" data-count="{{ (int) $totalNominal }}" data-prefix="Rp ">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                        @if ($delta)<div class="kpi-sub">{!! $deltaBadge($delta['nominal']) !!}</div>@endif
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index', $rangeParams + ['status' => 'sudah_dibayar']) }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile">
                    <span class="kpi-icon green"><i class="bi bi-check2-circle"></i></span>
                    <div class="kpi-body">
                        <div class="kpi-name">Sudah Dibayar</div>
                        <div class="kpi-num" data-count="{{ (int) $totalSudahDibayar }}" data-prefix="Rp ">Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}</div>
                        @if ($delta)<div class="kpi-sub">{!! $deltaBadge($delta['sudah']) !!}</div>@endif
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index', $rangeParams + ['status' => 'belum_dibayar']) }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile kpi-highlight">
                    <span class="kpi-icon"><i class="bi bi-hourglass-split"></i></span>
                    <div class="kpi-body">
                        <div class="kpi-name">Belum Dibayar</div>
                        <div class="kpi-num" data-count="{{ (int) $totalBelumDibayar }}" data-prefix="Rp ">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</div>
                        <div class="kpi-sub">
                            @if ($delta){!! $deltaBadge($delta['belum'], true, true) !!}@endif
                            @if ($jumlahJatuhTempo > 0)<small>{{ $jumlahJatuhTempo }} jatuh tempo</small>@endif
                        </div>
                    </div>
                    <svg class="kpi-wave" viewBox="0 0 100 40" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round">
                        <path d="M2 30 C 15 30, 15 8, 30 12 S 48 34, 62 22 S 82 4, 98 10"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>

    {{-- Baris 2: tren + status --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Tren Nominal</h6>
                        <small class="text-muted">{{ $trendGranularity === 'harian' ? 'Per hari' : 'Per bulan' }} &middot; klik titik untuk lihat datanya</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTren')" title="Unduh grafik"><i class="bi bi-download"></i></button>
                </div>
                <div class="chart-box"><canvas id="chartTren" style="cursor: pointer;"></canvas></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Status Pembayaran</h6>
                        <small class="text-muted">Klik bagian grafik</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartStatus')" title="Unduh grafik"><i class="bi bi-download"></i></button>
                </div>
                <div class="chart-box"><canvas id="chartStatus" style="cursor: pointer;"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Baris 3: kondisi saat ini (tidak mengikuti periode) --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-alarm"></i> Sudah Terlambat
                        <span class="badge bg-danger ms-1">{{ $jumlahTerlambat }}</span>
                    </h6>
                    <a href="{{ route('advertorials.index', ['status' => 'jatuh_tempo']) }}" class="small">Lihat semua</a>
                </div>
                <small class="text-muted d-block mb-2">Kondisi saat ini</small>

                <div class="list-scroll">
                    @forelse ($daftarTerlambat as $item)
                        <a href="{{ route('advertorials.index', ['status' => 'jatuh_tempo', 'company_id' => $item->company_id, 'media_id' => $item->media_id]) }}" class="notif-item">
                            <div style="min-width: 0;">
                                <div class="fw-semibold text-truncate">{{ $item->company->nama_perusahaan ?? '-' }}</div>
                                <small class="text-muted">{{ $item->media->nama_media ?? '-' }}</small>
                            </div>
                            <div class="text-end text-nowrap">
                                <div class="fw-semibold small">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                                <small class="text-danger fw-semibold">Terlambat {{ abs($item->umur_piutang ?? 0) }} hari</small>
                            </div>
                        </a>
                    @empty
                        <div class="empty-note"><i class="bi bi-check-circle"></i>Tidak ada tagihan yang terlambat.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="fw-bold mb-0" style="color: var(--warn-text);">
                        <i class="bi bi-hourglass-split"></i> Jatuh Tempo 7 Hari
                        <span class="badge ms-1" style="background: #d69e2e;">{{ $jumlahSegera }}</span>
                    </h6>
                    <a href="{{ route('advertorials.index', ['status' => 'belum_dibayar']) }}" class="small">Lihat semua</a>
                </div>
                <small class="text-muted d-block mb-2">Kondisi saat ini</small>

                <div class="list-scroll">
                    @forelse ($daftarSegera as $item)
                        <a href="{{ route('advertorials.index', ['status' => 'belum_dibayar', 'company_id' => $item->company_id, 'media_id' => $item->media_id]) }}" class="notif-item">
                            <div style="min-width: 0;">
                                <div class="fw-semibold text-truncate">{{ $item->company->nama_perusahaan ?? '-' }}</div>
                                <small class="text-muted">{{ $item->media->nama_media ?? '-' }}</small>
                            </div>
                            <div class="text-end text-nowrap">
                                <div class="fw-semibold small">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                                @if (($item->umur_piutang ?? 0) === 0)
                                    <small class="fw-semibold" style="color: var(--warn-text);">Hari ini</small>
                                @else
                                    <small class="fw-semibold" style="color: var(--warn-text);">{{ $item->umur_piutang }} hari lagi</small>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="empty-note"><i class="bi bi-check-circle"></i>Tidak ada yang jatuh tempo dalam 7 hari.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="fw-bold mb-0">Kuota &mdash; {{ now()->translatedFormat('F Y') }}</h6>
                    <a href="{{ route('media.index') }}" class="small">Kelola</a>
                </div>
                <small class="text-muted d-block mb-2">Bulan berjalan</small>

                <div class="list-scroll">
                    @forelse ($planBulanIni as $plan)
                        <a href="{{ route('advertorials.index', ['plan_id' => $plan->id]) }}" class="quota-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div style="min-width: 0;">
                                    <div class="fw-semibold text-truncate">{{ $plan->company->nama_perusahaan ?? '-' }}</div>
                                    <small class="text-muted">{{ $plan->media->nama_media ?? '-' }}</small>
                                </div>
                                <div class="text-end text-nowrap small">
                                    <span class="text-success fw-bold">{{ $plan->sisa_kuota }}</span> <span class="text-muted">sisa</span>
                                    &middot;
                                    <span class="text-danger fw-bold">{{ $plan->realisasi }}</span> <span class="text-muted">terpakai</span>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar {{ $plan->persentase >= 100 ? 'bg-success' : '' }}"
                                     style="width: {{ $plan->persentase }}%; background-color: {{ $plan->persentase >= 100 ? '' : '#3182ce' }};"></div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-note">
                            <i class="bi bi-clipboard-data" style="color: var(--muted);"></i>
                            Belum ada kuota bulan ini.<br>
                            <a href="{{ route('media.index') }}">Atur kuota</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 4: cetak vs online + top perusahaan --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Cetak vs Online</h6>
                        <small class="text-muted">Klik batang untuk lihat datanya</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTipe')" title="Unduh grafik"><i class="bi bi-download"></i></button>
                </div>
                <div class="chart-box short"><canvas id="chartTipe" style="cursor: pointer;"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Top 5 Perusahaan</h6>
                        <small class="text-muted">Klik batang untuk lihat datanya</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTopCompany')" title="Unduh grafik"><i class="bi bi-download"></i></button>
                </div>
                <div class="chart-box short"><canvas id="chartTopCompany" style="cursor: pointer;"></canvas></div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const urlAdvertorials = "{{ route('advertorials.index') }}";
    const rangeParams = @json($rangeParams);
    const trendRanges = @json($trendRanges);
    const statusKeys = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'];
    const tipeKeys = ['cetak', 'online'];
    const topCompanyIds = [@foreach ($topCompanies as $tc){{ $tc->company_id }},@endforeach];

    // Pindah ke halaman data dengan membawa periode yang sedang dipilih
    function goTo(extra) {
        const params = new URLSearchParams(Object.assign({}, rangeParams, extra));
        window.location.href = urlAdvertorials + '?' + params.toString();
    }

    const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    const rupiahShort = (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt';
    const toRgba = (hex, a) => {
        const n = parseInt(hex.replace('#', ''), 16);
        return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
    };

    let charts = [];

    // Grafik dibangun ulang setiap tema berganti, supaya warnanya selalu cocok
    function buildCharts() {
        charts.forEach((c) => c.destroy());
        charts = [];

        const accent = css('--accent');
        const primary = css('--primary');
        const grid = css('--line-soft');
        const success = '#38a169', warning = '#d69e2e', danger = '#e53e3e';

        Chart.defaults.color = css('--muted');

        // Tren
        const trenCtx = document.getElementById('chartTren').getContext('2d');
        const trenGrad = trenCtx.createLinearGradient(0, 0, 0, 260);
        trenGrad.addColorStop(0, toRgba(accent, .35));
        trenGrad.addColorStop(1, toRgba(accent, 0));

        charts.push(new Chart(trenCtx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'Total Nominal',
                    data: @json($trendValues),
                    borderColor: accent,
                    backgroundColor: trenGrad,
                    fill: true,
                    tension: 0.4,
                    pointRadius: @json(count($trendLabels) > 20 ? 2 : 5),
                    pointHoverRadius: 8,
                    pointBackgroundColor: css('--card-bg'),
                    pointBorderColor: accent,
                    pointBorderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'nearest', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: rupiahShort }, grid: { color: grid } },
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8, autoSkip: true } }
                },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const r = trendRanges[elements[0].index];
                        goTo({ dari_tanggal: r.dari, sampai_tanggal: r.sampai });
                    }
                }
            }
        }));

        // Status pembayaran
        charts.push(new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: ['Sudah Dibayar', 'Belum Dibayar', 'Jatuh Tempo'],
                datasets: [{
                    data: [{{ $statusData['sudah_dibayar'] }}, {{ $statusData['belum_dibayar'] }}, {{ $statusData['jatuh_tempo'] }}],
                    backgroundColor: [success, warning, danger],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, font: { size: 11 } } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) goTo({ status: statusKeys[elements[0].index] });
                }
            }
        }));

        // Cetak vs Online
        charts.push(new Chart(document.getElementById('chartTipe'), {
            type: 'bar',
            data: {
                labels: ['Media Cetak', 'Media Online'],
                datasets: [{
                    label: 'Total Nominal',
                    data: [{{ $tipeData['cetak'] }}, {{ $tipeData['online'] }}],
                    backgroundColor: [primary, accent],
                    borderRadius: 10,
                    maxBarThickness: 70
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: rupiahShort }, grid: { color: grid } }, x: { grid: { display: false } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) goTo({ tipe: tipeKeys[elements[0].index] });
                }
            }
        }));

        // Top 5 perusahaan
        charts.push(new Chart(document.getElementById('chartTopCompany'), {
            type: 'bar',
            data: {
                labels: [@foreach ($topCompanies as $tc)"{{ $tc->company->nama_perusahaan ?? 'N/A' }}",@endforeach],
                datasets: [{
                    label: 'Total Nominal',
                    data: [@foreach ($topCompanies as $tc){{ $tc->total }},@endforeach],
                    backgroundColor: accent,
                    borderRadius: 10,
                    maxBarThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { callback: rupiahShort }, grid: { color: grid } }, y: { grid: { display: false } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) goTo({ company_id: topCompanyIds[elements[0].index] });
                }
            }
        }));
    }

    buildCharts();
    document.addEventListener('themechange', buildCharts);

    function downloadChart(canvasId) {
        const canvas = document.getElementById(canvasId);
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png', 1.0);
        link.download = canvasId + '.png';
        link.click();
    }
</script>
@endpush