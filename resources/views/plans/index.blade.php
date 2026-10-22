@extends('layouts.app')
@section('title', 'Planning Iklan')

@section('content')
<div class="card-custom">
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-md-2">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select form-select-sm">
                <option value="">Semua Bulan</option>
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected(request('bulan') == $m)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Tahun</label>
            <input type="number" name="tahun" class="form-control form-control-sm" placeholder="Semua" value="{{ request('tahun') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Perusahaan</label>
            <select name="company_id" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(request('company_id') == $company->id)>{{ $company->nama_perusahaan }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Media</label>
            <select name="media_id" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($mediaList as $media)
                    <option value="{{ $media->id }}" @selected(request('media_id') == $media->id)>{{ $media->nama_media }} ({{ ucfirst($media->tipe) }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-sm btn-primary-custom w-100"><i class="bi bi-funnel"></i></button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('plans.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('plans.create') }}" class="btn btn-primary-custom btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Planning
        </a>
    </div>

    @forelse ($plans as $group)
        @php
            $first = $group->first();
            $labelPeriode = \Carbon\Carbon::create()->month($first->bulan)->translatedFormat('F') . ' ' . $first->tahun;
        @endphp

        <h6 class="fw-bold mt-4 mb-2">{{ $labelPeriode }}</h6>
        <div class="table-responsive mb-3">
            <table class="table table-custom align-middle">
<thead>
    <tr>
        <th>Perusahaan</th>
        <th>Media</th>
        <th>Kuota</th>
        <th>Realisasi</th>
        <th style="width: 180px;">Progress</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($group as $plan)
            <tr>
                <td class="fw-semibold">{{ $plan->company->nama_perusahaan }}</td>
                <td>{{ $plan->media->nama_media }} <span class="badge bg-light text-dark">{{ ucfirst($plan->media->tipe) }}</span></td>
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
                    <a href="{{ route('plans.show', $plan) }}" class="btn btn-sm btn-outline-info" title="Lihat detail"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus planning ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>            
    </table>
        </div>
    @empty
        <p class="text-center text-muted py-4">Belum ada planning iklan. Klik "Tambah Planning" untuk membuat yang pertama.</p>
    @endforelse
</div>
@endsection