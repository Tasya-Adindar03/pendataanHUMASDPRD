@extends('layouts.app')
@section('title', 'Kuota: ' . $media->nama_media)

@section('content')

<div class="card-custom mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1">{{ $media->nama_media }}</h5>
            <p class="text-muted mb-0">
                <span class="badge bg-light text-dark">{{ ucfirst($media->tipe) }}</span>
                @if ($media->rate_card)
                    &middot; Rate card Rp {{ number_format($media->rate_card, 0, ',', '.') }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('media.edit', $media) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit Media</a>
            <a href="{{ route('media.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </div>
</div>

<div class="card-custom mb-3">
    <h6 class="fw-bold mb-3">Tambah Kuota</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('media.kuota.store', $media) }}" method="POST" class="row g-2 align-items-end">
        @csrf

        <div class="col-md-4">
            <label class="form-label">Perusahaan</label>
            <select name="company_id" class="form-select" required>
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->nama_perusahaan }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select" required>
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected(old('bulan', now()->month) == $m)>{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Tahun</label>
            <input type="number" name="tahun" class="form-control" value="{{ old('tahun', now()->year) }}" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">Kuota</label>
            <input type="number" name="kuota" class="form-control" min="1" value="{{ old('kuota') }}" required>
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-primary-custom w-100" title="Tambah"><i class="bi bi-plus-lg"></i></button>
        </div>
    </form>
</div>

<div class="card-custom">
    <h6 class="fw-bold mb-3">Daftar Kuota</h6>

    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Perusahaan</th>
                    <th>Kuota</th>
                    <th>Realisasi</th>
                    <th style="width: 160px;">Progress</th>
                    <th>Total Kuota</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($plans as $plan)
                    <tr>
                        <td>{{ $plan->label_periode }}</td>
                        <td class="fw-semibold">{{ $plan->company->nama_perusahaan ?? '-' }}</td>
                        <td class="text-success fw-bold">{{ $plan->sisa_kuota }}</td>
                        <td class="text-danger fw-bold">{{ $plan->realisasi }}</td>
                        <td>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $plan->persentase >= 100 ? 'bg-success' : '' }}"
                                     style="width: {{ $plan->persentase }}%; background-color: {{ $plan->persentase >= 100 ? '' : '#3182ce' }};"></div>
                            </div>
                            <small class="text-muted">{{ $plan->persentase }}%</small>
                        </td>
                        <td>
                            <form action="{{ route('media.kuota.update', $plan) }}" method="POST" class="d-flex gap-1">
                                @csrf
                                @method('PUT')
                                <input type="number" name="kuota" min="1" value="{{ $plan->kuota }}" class="form-control form-control-sm" style="width: 80px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Simpan"><i class="bi bi-check-lg"></i></button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('advertorials.index', ['plan_id' => $plan->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat tayangan">
                                <i class="bi bi-list-ul"></i>
                            </a>
                            <form action="{{ route('media.kuota.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus kuota ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada kuota untuk media ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection