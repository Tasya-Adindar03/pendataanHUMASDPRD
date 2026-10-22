@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    {{-- KPI Cards (bisa diklik) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('advertorials.index') }}" class="kpi-card-link">
                <div class="card-custom kpi-card">
                    <div class="kpi-label"><i class="bi bi-stack"></i> Total Data</div>
                    <div class="kpi-value">{{ $totalData }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('advertorials.index') }}" class="kpi-card-link">
                <div class="card-custom kpi-card">
                    <div class="kpi-label"><i class="bi bi-cash-stack"></i> Total Nominal</div>
                    <div class="kpi-value">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('advertorials.index', ['status' => 'sudah_dibayar']) }}" class="kpi-card-link">
                <div class="card-custom kpi-card kpi-success">
                    <div class="kpi-label"><i class="bi bi-check-circle"></i> Sudah Dibayar</div>
                    <div class="kpi-value">Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('advertorials.index', ['status' => 'belum_dibayar']) }}" class="kpi-card-link">
                <div class="card-custom kpi-card kpi-warning">
                    <div class="kpi-label"><i class="bi bi-exclamation-circle"></i> Belum Dibayar</div>
                    <div class="kpi-value">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</div>
                    @if ($jumlahJatuhTempo > 0)
                        <a href="{{ route('advertorials.index', ['status' => 'jatuh_tempo']) }}" class="text-danger small d-block mt-1">
                            <i class="bi bi-arrow-right-short"></i> {{ $jumlahJatuhTempo }} data jatuh tempo
                        </a>
                    @endif
                </div>
            </a>
        </div>
    </div>

    {{-- Planning Iklan Bulan Ini --}}
    <div class="card-custom mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Planning Iklan — {{ now()->translatedFormat('F Y') }}</h6>
            <a href="{{ route('plans.index') }}" class="btn btn-sm btn-outline-secondary">
                Lihat Semua Planning <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if ($planBulanIni->isEmpty())
            <p class="text-muted text-center py-3 mb-0">Belum ada planning untuk bulan ini. <a href="{{ route('plans.create') }}">Buat planning baru</a>.</p>
        @else
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Perusahaan</th>
                            <th>Media</th>
                            <th>Kuota</th>
                            <th>Realisasi</th>
                            <th style="width: 200px;">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($planBulanIni as $plan)
                            <tr class="plan-row" onclick="window.location.href='{{ route('advertorials.index', ['company_id' => $plan->company_id, 'media_id' => $plan->media_id]) }}'">
                                <td class="fw-semibold">{{ $plan->company->nama_perusahaan }}</td>
                                <td>{{ $plan->media->nama_media }} <span class="badge bg-light text-dark">{{ ucfirst($plan->media->tipe) }}</span></td>
                                <td>{{ $plan->kuota }}</td>
                                <td>{{ $plan->realisasi }}</td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $plan->persentase >= 100 ? 'bg-success' : '' }}"
                                             style="width: {{ $plan->persentase }}%; background-color: {{ $plan->persentase >= 100 ? '' : '#3182ce' }};"></div>
                                    </div>
                                    <small class="text-muted">{{ $plan->persentase }}% ({{ $plan->sisa_kuota }} sisa)</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Charts Row 1 (bisa diklik) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Status Pembayaran</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartStatus')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <canvas id="chartStatus" style="cursor: pointer;"></canvas>
                <small class="text-muted d-block text-center mt-2">Klik bagian chart untuk lihat detail data</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Cetak vs Online</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTipe')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <canvas id="chartTipe" style="cursor: pointer;"></canvas>
                <small class="text-muted d-block text-center mt-2">Klik batang untuk lihat detail data</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Top 5 Perusahaan</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTopCompany')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <canvas id="chartTopCompany" style="cursor: pointer;"></canvas>
                <small class="text-muted d-block text-center mt-2">Klik batang untuk lihat detail perusahaan</small>
            </div>
        </div>
    </div>

    {{-- Chart Row 2: Tren --}}
    <div class="row g-3">
        <div class="col-md-12">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Tren Nominal 6 Bulan Terakhir</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTren')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <canvas id="chartTren" height="80" style="cursor: pointer;"></canvas>
                <small class="text-muted d-block text-center mt-2">Klik titik grafik untuk lihat data bulan tersebut</small>
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
    const urlCetak = "{{ route('advertorials.media', ['tipe' => 'cetak']) }}";
    const urlOnline = "{{ route('advertorials.media', ['tipe' => 'online']) }}";
    
    // Chart 1: Status Pembayaran (Donut) — klik untuk filter status
    const statusKeys = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'];
    const chartStatus = new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Sudah Dibayar', 'Belum Dibayar', 'Jatuh Tempo'],
            datasets: [{
                data: [
                    {{ $statusData['sudah_dibayar'] }},
                    {{ $statusData['belum_dibayar'] }},
                    {{ $statusData['jatuh_tempo'] }}
                ],
                backgroundColor: [successColor, warningColor, dangerColor],
                borderWidth: 0
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    const status = statusKeys[elements[0].index];
                    window.location.href = urlAdvertorials + '?status=' + status;
                }
            }
        }
    });

    // Chart 2: Cetak vs Online (Bar) — klik untuk buka halaman cetak/online
    const tipeUrls = [urlCetak, urlOnline];
    const chartTipe = new Chart(document.getElementById('chartTipe'), {
        type: 'bar',
        data: {
            labels: ['Media Cetak', 'Media Online'],
            datasets: [{
                label: 'Total Nominal',
                data: [{{ $tipeData['cetak'] }}, {{ $tipeData['online'] }}],
                backgroundColor: [primaryColor, accentColor],
                borderRadius: 6
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt' } }
            },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    window.location.href = tipeUrls[elements[0].index];
                }
            }
        }
    });

    // Chart 3: Top 5 Perusahaan — klik untuk filter perusahaan
    const topCompanyIds = [
        @foreach ($topCompanies as $tc)
            {{ $tc->company_id }},
        @endforeach
    ];
    const chartTopCompany = new Chart(document.getElementById('chartTopCompany'), {
        type: 'bar',
        data: {
            labels: [
                @foreach ($topCompanies as $tc)
                    "{{ $tc->company->nama_perusahaan ?? 'N/A' }}",
                @endforeach
            ],
            datasets: [{
                label: 'Total Nominal',
                data: [
                    @foreach ($topCompanies as $tc)
                        {{ $tc->total }},
                    @endforeach
                ],
                backgroundColor: accentColor,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    const companyId = topCompanyIds[elements[0].index];
                    window.location.href = urlAdvertorials + '?company_id=' + companyId;
                }
            }
        }
    });

    // Chart 4: Tren 6 Bulan (Line) — klik titik untuk filter rentang tanggal bulan itu
    const bulanRanges = @json($bulanRanges);
    const chartTren = new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: [@foreach ($bulanLabels as $label) "{{ $label }}", @endforeach],
            datasets: [{
                label: 'Total Nominal',
                data: [@foreach ($bulanNominal as $val) {{ $val }}, @endforeach],
                borderColor: accentColor,
                backgroundColor: 'rgba(49,130,206,0.1)',
                fill: true,
                tension: 0.35,
                pointRadius: 4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt' } }
            },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    const range = bulanRanges[elements[0].index];
                    window.location.href = urlAdvertorials + '?dari_tanggal=' + range.dari + '&sampai_tanggal=' + range.sampai;
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