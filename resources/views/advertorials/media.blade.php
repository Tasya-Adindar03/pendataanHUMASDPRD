@extends('layouts.app')
@section('title', 'Media Cetak & Online')

@section('content')
<div class="card-custom">

    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tipe === 'cetak' ? 'active' : '' }}" href="{{ route('advertorials.media', ['tipe' => 'cetak']) }}">
                <i class="bi bi-file-earmark-text"></i> Media Cetak
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tipe === 'online' ? 'active' : '' }}" href="{{ route('advertorials.media', ['tipe' => 'online']) }}">
                <i class="bi bi-globe"></i> Media Online
            </a>
        </li>
    </ul>

    @if ($quotaSummary->isNotEmpty())
        <h6 class="fw-bold mb-3">Kuota Perusahaan — {{ now()->translatedFormat('F Y') }} ({{ ucfirst($tipe) }})</h6>
        <div class="table-responsive mb-4">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th>Perusahaan</th>
                        <th>Kuota</th>
                        <th>Realisasi</th>
                        <th style="width: 200px;">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotaSummary as $summary)
                        <tr>
                            <td class="fw-semibold">{{ $summary->company->nama_perusahaan ?? '-' }}</td>
                            <td class="text-success fw-bold">{{ $summary->sisa }}</td>
                            <td class="text-danger fw-bold">{{ $summary->realisasi }}</td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $summary->persentase >= 100 ? 'bg-success' : '' }}"
                                         style="width: {{ $summary->persentase }}%; background-color: {{ $summary->persentase >= 100 ? '' : '#3182ce' }};"></div>
                                </div>
                                <small class="text-muted">{{ $summary->persentase }}%</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="d-flex justify-content-end mb-3">
        @include('advertorials._export-buttons')
    </div>

    @include('advertorials._filter')
    @include('advertorials._table')
</div>
@endsection