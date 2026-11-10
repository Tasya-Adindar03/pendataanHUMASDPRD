@php
    $defaultTipe = $tipe ?? '';
    $defaultStatus = request('status', '');
    $keepFilters = request()->only(['company_id', 'media_id', 'plan_id', 'dari_tanggal', 'sampai_tanggal', 'search']);
@endphp

<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
        <i class="bi bi-download"></i> Unduh Laporan
    </button>

    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 290px;">
        <form method="GET">
            @foreach ($keepFilters as $key => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <div class="mb-2">
                <label class="form-label small mb-1">Jenis Media</label>
                <select name="tipe" class="form-select form-select-sm">
                    <option value="" @selected($defaultTipe === '')>Semua (Cetak &amp; Online)</option>
                    <option value="cetak" @selected($defaultTipe === 'cetak')>Media Cetak saja</option>
                    <option value="online" @selected($defaultTipe === 'online')>Media Online saja</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small mb-1">Status Pembayaran</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="" @selected($defaultStatus === '')>Semua Status (Gabungan)</option>
                    <option value="sudah_dibayar" @selected($defaultStatus === 'sudah_dibayar')>Sudah Dibayar saja</option>
                    <option value="belum_dibayar" @selected($defaultStatus === 'belum_dibayar')>Belum Dibayar saja</option>
                    <option value="jatuh_tempo" @selected($defaultStatus === 'jatuh_tempo')>Jatuh Tempo saja</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" formaction="{{ route('export.pdf') }}" class="btn btn-sm btn-danger w-100">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
                <button type="submit" formaction="{{ route('export.excel') }}" class="btn btn-sm btn-success w-100">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
            </div>
        </form>
    </div>
</div>