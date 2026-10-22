@php
    $baseParams = request()->except(['status', 'page']);
    if (isset($tipe)) {
        $baseParams['tipe'] = $tipe;
    }
@endphp

<div class="dropdown d-inline-block">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-download"></i> Unduh Laporan
    </button>
    <ul class="dropdown-menu">
        <li><h6 class="dropdown-header">PDF</h6></li>
        <li><a class="dropdown-item" href="{{ route('export.pdf', $baseParams) }}">Gabungan (Semua Status)</a></li>
        <li><a class="dropdown-item" href="{{ route('export.pdf', array_merge($baseParams, ['status' => 'sudah_dibayar'])) }}">Sudah Dibayar Saja</a></li>
        <li><a class="dropdown-item" href="{{ route('export.pdf', array_merge($baseParams, ['status' => 'belum_dibayar'])) }}">Belum Dibayar Saja</a></li>
        <li><a class="dropdown-item" href="{{ route('export.pdf', array_merge($baseParams, ['status' => 'jatuh_tempo'])) }}">Jatuh Tempo Saja</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><h6 class="dropdown-header">Excel</h6></li>
        <li><a class="dropdown-item" href="{{ route('export.excel', $baseParams) }}">Gabungan (Semua Status)</a></li>
        <li><a class="dropdown-item" href="{{ route('export.excel', array_merge($baseParams, ['status' => 'sudah_dibayar'])) }}">Sudah Dibayar Saja</a></li>
        <li><a class="dropdown-item" href="{{ route('export.excel', array_merge($baseParams, ['status' => 'belum_dibayar'])) }}">Belum Dibayar Saja</a></li>
        <li><a class="dropdown-item" href="{{ route('export.excel', array_merge($baseParams, ['status' => 'jatuh_tempo'])) }}">Jatuh Tempo Saja</a></li>
    </ul>
</div>