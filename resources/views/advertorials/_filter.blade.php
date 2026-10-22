<form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end mb-4">
    @isset($tipe)
        <input type="hidden" name="tipe" value="{{ $tipe }}">
    @endisset

    <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua</option>
            <option value="belum_dibayar" @selected(request('status') === 'belum_dibayar')>Belum Dibayar</option>
            <option value="sudah_dibayar" @selected(request('status') === 'sudah_dibayar')>Sudah Dibayar</option>
            <option value="jatuh_tempo" @selected(request('status') === 'jatuh_tempo')>Jatuh Tempo</option>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Perusahaan</label>
        <select name="company_id" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected(request('company_id') == $company->id)>{{ $company->nama_perusahaan }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Media</label>
        <select name="media_id" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach ($mediaList as $media)
                <option value="{{ $media->id }}" @selected(request('media_id') == $media->id)>{{ $media->nama_media }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" name="dari_tanggal" class="form-control form-control-sm" value="{{ request('dari_tanggal') }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" name="sampai_tanggal" class="form-control form-control-sm" value="{{ request('sampai_tanggal') }}">
    </div>

    <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary-custom w-100">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <a href="{{ url()->current() }}{{ isset($tipe) ? '?tipe='.$tipe : '' }}" class="btn btn-sm btn-outline-secondary">Reset</a>
    </div>
</form>