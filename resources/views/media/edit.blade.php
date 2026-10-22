@extends('layouts.app')
@section('title', 'Edit Media')

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

        <form action="{{ route('media.update', $media) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Media</label>
                <input type="text" name="nama_media" class="form-control" value="{{ old('nama_media', $media->nama_media) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Tipe Media</label>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="tipe" id="tipe_cetak" value="cetak" {{ old('tipe', $media->tipe) === 'cetak' ? 'checked' : '' }} required>
                    <label class="btn btn-outline-secondary" for="tipe_cetak"><i class="bi bi-file-earmark-text"></i> Cetak</label>

                    <input type="radio" class="btn-check" name="tipe" id="tipe_online" value="online" {{ old('tipe', $media->tipe) === 'online' ? 'checked' : '' }} required>
                    <label class="btn btn-outline-secondary" for="tipe_online"><i class="bi bi-globe"></i> Online</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Rate Card (opsional)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="rate_display" class="form-control" inputmode="numeric" autocomplete="off">
                    <input type="hidden" name="rate_card" id="rate_card" value="{{ old('rate_card', (int) $media->rate_card) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Kontak PIC (opsional)</label>
                <input type="text" name="pic_kontak" class="form-control" value="{{ old('pic_kontak', $media->pic_kontak) }}">
            </div>

            <button type="submit" class="btn btn-primary-custom">Update</button>
            <a href="{{ route('media.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
@endsection

@push('scripts')
    @include('partials.rupiah-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initRupiahInput('rate_display', 'rate_card');
        });
    </script>
@endpush