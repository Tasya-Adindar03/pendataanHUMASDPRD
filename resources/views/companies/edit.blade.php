@extends('layouts.app')
@section('title', 'Edit Perusahaan')

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

        <form action="{{ route('companies.update', $company) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Perusahaan</label>
                <input type="text" name="nama_perusahaan" class="form-control" value="{{ old('nama_perusahaan', $company->nama_perusahaan) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama PIC</label>
                <input type="text" name="pic_nama" class="form-control" value="{{ old('pic_nama', $company->pic_nama) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Kontak PIC</label>
                <input type="text" name="pic_kontak" class="form-control" value="{{ old('pic_kontak', $company->pic_kontak) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $company->alamat) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary-custom">Update</button>
            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
@endsection