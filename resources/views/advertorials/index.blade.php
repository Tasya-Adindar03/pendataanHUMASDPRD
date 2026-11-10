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

        @include('advertorials._export-buttons')
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
            <table class="table table-custom align-middle">
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
                            <td class="fw-semibold">{{ $plan->company->nama_perusahaan ?? '-' }}</td>
                            <td>{{ $plan->media->nama_media ?? '-' }}</td>
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
                                <a href="{{ route('advertorials.index', ['plan_id' => $plan->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat tayangan">
                                    <i class="bi bi-list-ul"></i>
                                </a>
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