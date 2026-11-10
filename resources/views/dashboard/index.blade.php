@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Halo, ' . auth()->user()->name . '!')

@section('content')

    <style>
        .kpi-tile { display: flex; align-items: center; gap: 14px; padding: 1.1rem 1.25rem; height: 100%; }
        .kpi-icon { width: 48px; height: 48px; border-radius: 50%; display: grid; place-items: center; font-size: 1.25rem; background: #eef2f7; color: var(--primary); flex-shrink: 0; }
        .kpi-icon.green { background: #def7ec; color: var(--success); }
        .kpi-name { font-size: .8rem; color: var(--muted); font-weight: 600; }
        .kpi-num { font-size: 1.35rem; font-weight: 700; line-height: 1.25; }

        .kpi-highlight { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #fff; position: relative; overflow: hidden; }
        .kpi-highlight .kpi-name { color: rgba(255,255,255,.75); }
        .kpi-highlight .kpi-icon { background: rgba(255,255,255,.18); color: #fff; }
        .kpi-highlight small { color: rgba(255,255,255,.75); }
        .kpi-wave { position: absolute; right: 10px; bottom: 8px; width: 90px; opacity: .45; }

        .chart-box { position: relative; height: 270px; }
        .chart-box.short { height: 230px; }

        .notif-item, .quota-item {
            display: block; padding: .75rem .6rem; border-bottom: 1px solid #edf2f7;
            text-decoration: none; color: inherit; border-radius: 12px; transition: background .15s, transform .15s;
        }
        .notif-item { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
        .notif-item:last-child, .quota-item:last-child { border-bottom: none; }
        .notif-item:hover, .quota-item:hover { background: #f8fafc; color: inherit; transform: translateX(3px); }
        .list-scroll { max-height: 330px; overflow-y: auto; }
        .empty-note { text-align: center; color: var(--muted); padding: 2rem 0; }
        .empty-note i { font-size: 1.8rem; display: block; margin-bottom: .4rem; color: var(--success); }
    </style>

    {{-- Baris 1: kartu angka --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index') }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile">
                    <span class="kpi-icon"><i class="bi bi-stack"></i></span>
                    <div>
                        <div class="kpi-name">Total Data</div>
                        <div class="kpi-num" data-count="{{ $totalData }}">{{ $totalData }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index') }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile">
                    <span class="kpi-icon"><i class="bi bi-cash-stack"></i></span>
                    <div>
                        <div class="kpi-name">Total Nominal</div>
                        <div class="kpi-num" data-count="{{ (int) $totalNominal }}" data-prefix="Rp ">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index', ['status' => 'sudah_dibayar']) }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile">
                    <span class="kpi-icon green"><i class="bi bi-check2-circle"></i></span>
                    <div>
                        <div class="kpi-name">Sudah Dibayar</div>
                        <div class="kpi-num" data-count="{{ (int) $totalSudahDibayar }}" data-prefix="Rp ">Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('advertorials.index', ['status' => 'belum_dibayar']) }}" class="kpi-card-link h-100">
                <div class="card-custom kpi-tile kpi-highlight">
                    <span class="kpi-icon"><i class="bi bi-hourglass-split"></i></span>
                    <div>
                        <div class="kpi-name">Belum Dibayar</div>
                        <div class="kpi-num" data-count="{{ (int) $totalBelumDibayar }}" data-prefix="Rp ">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</div>
                        @if ($jumlahJatuhTempo > 0)
                            <small>{{ $jumlahJatuhTempo }} jatuh tempo</small>
                        @endif
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
                        <small class="text-muted">6 bulan terakhir &middot; klik titik untuk lihat datanya</small>
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

    {{-- Baris 3: perlu perhatian + kuota --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-alarm"></i> Sudah Terlambat
                        <span class="badge bg-danger ms-1">{{ $jumlahTerlambat }}</span>
                    </h6>
                    <a href="{{ route('advertorials.index', ['status' => 'jatuh_tempo']) }}" class="small">Lihat semua</a>
                </div>

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
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0" style="color: #b45309;">
                        <i class="bi bi-hourglass-split"></i> Jatuh Tempo 7 Hari
                        <span class="badge ms-1" style="background: #d69e2e;">{{ $jumlahSegera }}</span>
                    </h6>
                    <a href="{{ route('advertorials.index', ['status' => 'belum_dibayar']) }}" class="small">Lihat semua</a>
                </div>

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
                                    <small class="fw-semibold" style="color: #b45309;">Hari ini</small>
                                @else
                                    <small class="fw-semibold" style="color: #b45309;">{{ $item->umur_piutang }} hari lagi</small>
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
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">Kuota &mdash; {{ now()->translatedFormat('F Y') }}</h6>
                    <a href="{{ route('media.index') }}" class="small">Kelola</a>
                </div>

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
    const primaryColor = '#1e3a5f';
    const accentColor = '#3182ce';
    const successColor = '#38a169';
    const warningColor = '#d69e2e';
    const dangerColor = '#e53e3e';

    const urlAdvertorials = "{{ route('advertorials.index') }}";
    const urlCetak = "{{ route('advertorials.index', ['tipe' => 'cetak']) }}";
    const urlOnline = "{{ route('advertorials.index', ['tipe' => 'online']) }}";
    const rupiahShort = (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt';

    // Tren 6 bulan (garis dengan warna memudar)
    const bulanRanges = @json($bulanRanges);
    const trenCtx = document.getElementById('chartTren').getContext('2d');
    const trenGrad = trenCtx.createLinearGradient(0, 0, 0, 260);
    trenGrad.addColorStop(0, 'rgba(49,130,206,.35)');
    trenGrad.addColorStop(1, 'rgba(49,130,206,0)');

    new Chart(trenCtx, {
        type: 'line',
        data: {
            labels: [@foreach ($bulanLabels as $label)"{{ $label }}",@endforeach],
            datasets: [{
                label: 'Total Nominal',
                data: [@foreach ($bulanNominal as $val){{ $val }},@endforeach],
                borderColor: accentColor,
                backgroundColor: trenGrad,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: '#fff',
                pointBorderColor: accentColor,
                pointBorderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: rupiahShort }, grid: { color: '#edf2f7' } },
                x: { grid: { display: false } }
            },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    const range = bulanRanges[elements[0].index];
                    window.location.href = urlAdvertorials + '?dari_tanggal=' + range.dari + '&sampai_tanggal=' + range.sampai;
                }
            }
        }
    });

    // Status pembayaran (donut)
    const statusKeys = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'];
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Sudah Dibayar', 'Belum Dibayar', 'Jatuh Tempo'],
            datasets: [{
                data: [{{ $statusData['sudah_dibayar'] }}, {{ $statusData['belum_dibayar'] }}, {{ $statusData['jatuh_tempo'] }}],
                backgroundColor: [successColor, warningColor, dangerColor],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, font: { size: 11 } } } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    window.location.href = urlAdvertorials + '?status=' + statusKeys[elements[0].index];
                }
            }
        }
    });

    // Cetak vs Online (batang)
    const tipeUrls = [urlCetak, urlOnline];
    new Chart(document.getElementById('chartTipe'), {
        type: 'bar',
        data: {
            labels: ['Media Cetak', 'Media Online'],
            datasets: [{
                label: 'Total Nominal',
                data: [{{ $tipeData['cetak'] }}, {{ $tipeData['online'] }}],
                backgroundColor: [primaryColor, accentColor],
                borderRadius: 10,
                maxBarThickness: 70
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { ticks: { callback: rupiahShort }, grid: { color: '#edf2f7' } }, x: { grid: { display: false } } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    window.location.href = tipeUrls[elements[0].index];
                }
            }
        }
    });

    // Top 5 perusahaan (batang mendatar)
    const topCompanyIds = [@foreach ($topCompanies as $tc){{ $tc->company_id }},@endforeach];
    new Chart(document.getElementById('chartTopCompany'), {
        type: 'bar',
        data: {
            labels: [@foreach ($topCompanies as $tc)"{{ $tc->company->nama_perusahaan ?? 'N/A' }}",@endforeach],
            datasets: [{
                label: 'Total Nominal',
                data: [@foreach ($topCompanies as $tc){{ $tc->total }},@endforeach],
                backgroundColor: accentColor,
                borderRadius: 10,
                maxBarThickness: 28
            }]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { ticks: { callback: rupiahShort }, grid: { color: '#edf2f7' } }, y: { grid: { display: false } } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    window.location.href = urlAdvertorials + '?company_id=' + topCompanyIds[elements[0].index];
                }
            }
        }
    });

    function downloadChart(canvasId) {
        const canvas = document.getElementById(canvasId);
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png', 1.0);
        link.download = canvasId + '.png';
        link.click();
    }
</script>
@endpush