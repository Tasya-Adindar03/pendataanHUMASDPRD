@extends('layouts.app')
@section('title', 'Detail Rencana Iklan')

@section('content')
<div class="card-custom mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">{{ $plan->company->nama_perusahaan ?? 'Perusahaan dihapus' }}</h2>
            <p class="text-muted mb-0">{{ $plan->media->nama_media ?? 'Media dihapus' }} · {{ \Carbon\Carbon::create($plan->tahun, $plan->bulan, 1)->locale('id')->translatedFormat('F Y') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('plans.index', ['bulan' => $plan->bulan, 'tahun' => $plan->tahun]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali ke daftar</a>
            <a href="{{ route('plans.edit', $plan) }}" class="btn btn-sm btn-primary-custom"><i class="bi bi-pencil"></i> Edit rencana</a>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-6 col-lg-3"><div class="small text-muted">Kuota</div><strong>{{ $plan->kuota }} publikasi</strong></div>
        <div class="col-6 col-lg-3"><div class="small text-muted">Realisasi</div><strong>{{ $plan->advertorials_count }} publikasi</strong></div>
        <div class="col-6 col-lg-3"><div class="small text-muted">Sisa kuota</div><strong>{{ max(0, $plan->kuota - $plan->advertorials_count) }} publikasi</strong></div>
        <div class="col-6 col-lg-3"><div class="small text-muted">Nilai advertorial</div><strong>Rp {{ number_format($totalNominal, 0, ',', '.') }}</strong></div>
    </div>
    @php($progress = $plan->kuota > 0 ? min(100, round($plan->advertorials_count / $plan->kuota * 100)) : 100)
    <div class="d-flex justify-content-between small mt-3 mb-1"><span>Capaian kuota</span><strong>{{ $progress }}%</strong></div>
    <div class="progress" style="height:8px" role="progressbar" aria-label="Capaian kuota" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar {{ $progress >= 100 ? 'bg-success' : '' }}" style="width:{{ $progress }}%"></div></div>
    @if ($plan->catatan)
        <p class="small text-muted mt-3 mb-0"><strong>Catatan:</strong> {{ $plan->catatan }}</p>
    @endif
</div>
<div class="card-custom">
    <h2 class="h6 fw-bold mb-3">Advertorial terkait rencana</h2>
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead><tr><th>No. invoice</th><th>Judul</th><th>Tayang</th><th>Jatuh tempo</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($advertorials as $item)
                    <tr>
                        <td class="fw-semibold text-nowrap">{{ $item->no_invoice }}</td>
                        <td>{{ $item->judul }}</td>
                        <td class="text-nowrap">{{ $item->waktu_tayang?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-nowrap">{{ $item->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-nowrap">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td class="text-nowrap"><span class="badge-status {{ $item->status === 'sudah_dibayar' ? 'badge-sudah' : ($item->status === 'jatuh_tempo' ? 'badge-jatuh' : 'badge-belum') }}">{{ ['sudah_dibayar' => 'Sudah dibayar', 'belum_dibayar' => 'Belum dibayar', 'jatuh_tempo' => 'Jatuh tempo'][$item->status] }}</span></td>
                        <td><a href="{{ route('advertorials.edit', $item) }}" class="btn btn-sm btn-outline-primary" aria-label="Edit advertorial {{ $item->no_invoice }}"><i class="bi bi-pencil"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada advertorial yang terhubung dengan rencana ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $advertorials->onEachSide(1)->links() }}
</div>
@endsection
