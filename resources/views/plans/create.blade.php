@extends('layouts.app')
@section('title', 'Tambah Planning Iklan')

@section('content')
<div class="card-custom" style="max-width: 600px;">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('plans.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Perusahaan</label>
            <select name="company_id" class="form-select" required>
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->nama_perusahaan }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Media</label>
            <select name="media_id" class="form-select" required>
                <option value="">-- Pilih Media --</option>
                @foreach ($mediaList as $media)
                    <option value="{{ $media->id }}" @selected(old('media_id') == $media->id)>{{ $media->nama_media }} ({{ ucfirst($media->tipe) }})</option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select" required>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" @selected(old('bulan', now()->month) == $m)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ old('tahun', now()->year) }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Kuota Tayangan</label>
            <input type="number" name="kuota" class="form-control" min="1" value="{{ old('kuota') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea name="catatan" class="form-control" rows="3">{{ old('catatan') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary-custom">Simpan</button>
        <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary">Batal</a>
    </form>
</div>
@endsection