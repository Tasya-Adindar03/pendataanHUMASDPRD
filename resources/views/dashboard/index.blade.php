@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    <style>
        .notif-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.7rem 0.5rem;
            border-bottom: 1px solid #edf2f7;
            text-decoration: none;
            color: inherit;
            border-radius: 8px;
            transition: background 0.15s ease;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: #f8fafc; color: inherit; }
    </style>

    {{-- Banner peringatan --}}
    @if ($jumlahTerlambat > 0)
        <div class="alert alert-danger d-flex justify-content-between align-items-center mb-4">
            <div>
                <i class="bi bi-exclamation-triangle-fill"></i>
                Ada <strong>{{ $jumlahTerlambat }}</strong> tagihan terlambat dibayar
                senilai <strong>Rp {{ number_format($nominalTerlambat, 0, ',', '.') }}</strong>.
            </div>
            <a href="{{ route('advertorials.index', ['status' => 'jatuh_tempo']) }}" class="btn btn-sm btn-danger">Lihat Semua</a>
        </div>
    @endif

    {{-- KPI Cards --}}
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
                </div>
            </a>
        </div>
    </div>

    {{-- Notifikasi tenggat --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-alarm"></i> Sudah Terlambat
                        <span class="badge bg-danger ms-1">{{ $jumlahTerlambat }}</span>
                    </h6>
                    <a href="{{ route('advertorials.index', ['status' => 'jatuh_tempo']) }}" class="small">Lihat semua</a>
                </div>

                @forelse ($daftarTerlambat as $item)
                    <a href="{{ route('advertorials.index', ['status' => 'jatuh_tempo', 'company_id' => $item->company_id, 'media_id' => $item->media_id]) }}" class="notif-item">
                        <div>
                            <div class="fw-semibold">
                                {{ $item->company->nama_perusahaan }}
                                <span class="text-muted fw-normal">&middot; {{ $item->media->nama_media }}</span>
                            </div>
                            <small class="text-muted">{{ $item->judul }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                            <small class="text-danger fw-semibold">Terlambat {{ abs($item->umur_piutang ?? 0) }} hari</small>
                        </div>
                    </a>
                @empty
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="bi bi-check-circle text-success"></i> Tidak ada tagihan yang terlambat.
                    </p>
                @endforelse
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-custom h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0" style="color: #b45309;">
                        <i class="bi bi-hourglass-split"></i> Jatuh Tempo 7 Hari ke Depan
                        <span class="badge ms-1" style="background: #d69e2e;">{{ $jumlahSegera }}</span>
                    </h6>
                    <a href="{{ route('advertorials.index', ['status' => 'belum_dibayar']) }}" class="small">Lihat semua</a>
                </div>

                @forelse ($daftarSegera as $item)
                    <a href="{{ route('advertorials.index', ['status' => 'belum_dibayar', 'company_id' => $item->company_id, 'media_id' => $item->media_id]) }}" class="notif-item">
                        <div>
                            <div class="fw-semibold">
                                {{ $item->company->nama_perusahaan }}
                                <span class="text-muted fw-normal">&middot; {{ $item->media->nama_media }}</span>
                            </div>
                            <small class="text-muted">{{ $item->judul }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                            @if (($item->umur_piutang ?? 0) === 0)
                                <small class="fw-semibold" style="color: #b45309;">Hari ini</small>
                            @else
                                <small class="fw-semibold" style="color: #b45309;">{{ $item->umur_piutang }} hari lagi</small>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="bi bi-check-circle text-success"></i> Tidak ada tagihan yang jatuh tempo dalam 7 hari ke depan.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Kuota Iklan Bulan Ini --}}
    <div class="card-custom mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Kuota Iklan &mdash; {{ now()->translatedFormat('F Y') }}</h6>
            <a href="{{ route('media.index') }}" class="btn btn-sm btn-outline-secondary">
                Kelola Kuota <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if ($planBulanIni->isEmpty())
            <p class="text-muted text-center py-3 mb-0">Belum ada kuota untuk bulan ini. Atur lewat <a href="{{ route('media.index') }}">Kelola Media &amp; Kuota</a>.</p>
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
                            <tr class="plan-row" onclick="window.location.href='{{ route('advertorials.index', ['plan_id' => $plan->id]) }}'">
                                <td class="fw-semibold">{{ $plan->company->nama_perusahaan ?? '-' }}</td>
                                <td>{{ $plan->media->nama_media ?? '-' }} <span class="badge bg-light text-dark">{{ ucfirst($plan->media->tipe ?? '') }}</span></td>
                                <td class="text-success fw-bold">{{ $plan->sisa_kuota }}</td>
                                <td class="text-danger fw-bold">{{ $plan->realisasi }}</td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $plan->persentase >= 100 ? 'bg-success' : '' }}"
                                             style="width: {{ $plan->persentase }}%; background-color: {{ $plan->persentase >= 100 ? '' : '#3182ce' }};"></div>
                                    </div>
                                    <small class="text-muted">{{ $plan->persentase }}%</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Charts baris 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Status Pembayaran</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartStatus')"><i class="bi bi-download"></i></button>
                </div>
                <canvas id="chartStatus" style="cursor: pointer;"></canvas>
                <small class="text-muted d-block text-center mt-2">Klik bagian chart untuk lihat detail data</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Cetak vs Online</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTipe')"><i class="bi bi-download"></i></button>
                </div>
                <canvas id="chartTipe" style="cursor: pointer;"></canvas>
                <small class="text-muted d-block text-center mt-2">Klik batang untuk lihat detail data</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Top 5 Perusahaan</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTopCompany')"><i class="bi bi-download"></i></button>
                </div>
                <canvas id="chartTopCompany" style="cursor: pointer;"></canvas>
                <small class="text-muted d-block text-center mt-2">Klik batang untuk lihat detail perusahaan</small>
            </div>
        </div>
    </div>

    {{-- Chart tren --}}
    <div class="row g-3">
        <div class="col-md-12">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Tren Nominal 6 Bulan Terakhir</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('chartTren')"><i class="bi bi-download"></i></button>
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
    const urlCetak = "{{ route('advertorials.index', ['tipe' => 'cetak']) }}";
    const urlOnline = "{{ route('advertorials.index', ['tipe' => 'online']) }}";

    // Status Pembayaran (donut)
    const statusKeys = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'];
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Sudah Dibayar', 'Belum Dibayar', 'Jatuh Tempo'],
            datasets: [{
                data: [{{ $statusData['sudah_dibayar'] }}, {{ $statusData['belum_dibayar'] }}, {{ $statusData['jatuh_tempo'] }}],
                backgroundColor: [successColor, warningColor, dangerColor],
                borderWidth: 0
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    window.location.href = urlAdvertorials + '?status=' + statusKeys[elements[0].index];
                }
            }
        }
    });

    // Cetak vs Online (bar)
    const tipeUrls = [urlCetak, urlOnline];
    new Chart(document.getElementById('chartTipe'), {
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
            scales: { y: { ticks: { callback: (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt' } } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    window.location.href = tipeUrls[elements[0].index];
                }
            }
        }
    });

    // Top 5 perusahaan (horizontal bar)
    const topCompanyIds = [@foreach ($topCompanies as $tc){{ $tc->company_id }},@endforeach];
    new Chart(document.getElementById('chartTopCompany'), {
        type: 'bar',
        data: {
            labels: [@foreach ($topCompanies as $tc)"{{ $tc->company->nama_perusahaan ?? 'N/A' }}",@endforeach],
            datasets: [{
                label: 'Total Nominal',
                data: [@foreach ($topCompanies as $tc){{ $tc->total }},@endforeach],
                backgroundColor: accentColor,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    window.location.href = urlAdvertorials + '?company_id=' + topCompanyIds[elements[0].index];
                }
            }
        }
    });

    // Tren 6 bulan (line)
    const bulanRanges = @json($bulanRanges);
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: [@foreach ($bulanLabels as $label)"{{ $label }}",@endforeach],
            datasets: [{
                label: 'Total Nominal',
                data: [@foreach ($bulanNominal as $val){{ $val }},@endforeach],
                borderColor: accentColor,
                backgroundColor: 'rgba(49,130,206,0.1)',
                fill: true,
                tension: 0.35,
                pointRadius: 4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { ticks: { callback: (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt' } } },
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