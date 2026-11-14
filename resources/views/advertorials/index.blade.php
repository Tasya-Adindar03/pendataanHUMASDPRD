@extends('layouts.app')
@section('title', 'Media Cetak & Online')

@section('content')
@php
    $tabParams = request()->except(['tipe', 'page', 'media_id']);
@endphp

<div class="card-custom">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ empty($tipe) ? 'active' : '' }}" href="{{ route('advertorials.index', $tabParams) }}">
                    <i class="bi bi-grid"></i> Semua
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tipe === 'cetak' ? 'active' : '' }}" href="{{ route('advertorials.index', array_merge($tabParams, ['tipe' => 'cetak'])) }}">
                    <i class="bi bi-file-earmark-text"></i> Media Cetak
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tipe === 'online' ? 'active' : '' }}" href="{{ route('advertorials.index', array_merge($tabParams, ['tipe' => 'online'])) }}">
                    <i class="bi bi-globe"></i> Media Online
                </a>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
            @include('advertorials._export-buttons')
            <a href="{{ route('advertorials.create') }}" class="btn btn-sm btn-primary-custom py-1 px-3">
                <i class="bi bi-plus-lg"></i> Tambah Data
            </a>
        </div>
    </div>

    @if (request('plan_id'))
        <div class="alert alert-info py-2 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-funnel"></i> Menampilkan tayangan untuk satu kuota tertentu.</span>
            <a href="{{ route('advertorials.index', request()->except(['plan_id', 'page'])) }}" class="small">Tampilkan semua</a>
        </div>
    @endif

    @if ($quotaSummary->isNotEmpty())
        <h6 class="fw-bold mb-2">Kuota {{ now()->translatedFormat('F Y') }}</h6>
        <div class="table-responsive mb-4">
            <table class="table table-custom table-stack align-middle">
                <thead>
                    <tr>
                        <th>Perusahaan</th>
                        <th>Media</th>
                        <th>Kuota</th>
                        <th>Realisasi</th>
                        <th style="width: 180px;">Progress</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotaSummary as $plan)
                        <tr>
                            <td data-label="Perusahaan" class="fw-semibold"><div class="cell">{{ $plan->company->nama_perusahaan ?? '-' }}</div></td>
                            <td data-label="Media"><div class="cell">{{ $plan->media->nama_media ?? '-' }}</div></td>
                            <td data-label="Kuota" class="text-success fw-bold"><div class="cell">{{ $plan->sisa_kuota }}</div></td>
                            <td data-label="Realisasi" class="text-danger fw-bold"><div class="cell">{{ $plan->realisasi }}</div></td>
                            <td data-label="Progress">
                                <div class="cell" style="min-width: 120px;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $plan->persentase >= 100 ? 'bg-success' : '' }}"
                                             style="width: {{ $plan->persentase }}%; background-color: {{ $plan->persentase >= 100 ? '' : '#3182ce' }};"></div>
                                    </div>
                                    <small class="text-muted">{{ $plan->persentase }}%</small>
                                </div>
                            </td>
                            <td data-label="Aksi">
                                <div class="cell">
                                    <a href="{{ route('advertorials.index', ['plan_id' => $plan->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat tayangan">
                                        <i class="bi bi-list-ul"></i> <span class="d-md-none">Lihat tayangan</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @include('advertorials._filter')
    @include('advertorials._table')
</div>
@endsection