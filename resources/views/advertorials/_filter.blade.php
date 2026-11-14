@php
    $filterActive = collect(['status', 'company_id', 'media_id', 'dari_tanggal', 'sampai_tanggal'])
        ->contains(fn ($key) => request()->filled($key));
@endphp

<button class="btn btn-sm btn-outline-secondary d-md-none w-100 mb-3" type="button"
        data-bs-toggle="collapse" data-bs-target="#filterBox" aria-expanded="{{ $filterActive ? 'true' : 'false' }}">
    <i class="bi bi-funnel"></i> Filter {{ $filterActive ? '(aktif)' : '' }}
</button>

<div class="collapse d-md-block {{ $filterActive ? 'show' : '' }}" id="filterBox">
    <form method="GET" action="{{ route('advertorials.index') }}" class="row g-2 align-items-end mb-4">
        @if (!empty($tipe))
            <input type="hidden" name="tipe" value="{{ $tipe }}">
        @endif
        @if (request('plan_id'))
            <input type="hidden" name="plan_id" value="{{ request('plan_id') }}">
        @endif
        @if (request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif

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

        <div class="col-6 col-md-2">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="dari_tanggal" class="form-control form-control-sm" value="{{ request('dari_tanggal') }}">
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="sampai_tanggal" class="form-control form-control-sm" value="{{ request('sampai_tanggal') }}">
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary-custom w-100">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ route('advertorials.index', !empty($tipe) ? ['tipe' => $tipe] : []) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>