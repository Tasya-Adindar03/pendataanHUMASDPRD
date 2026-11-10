@extends('layouts.app')
@section('title', 'Tambah User')

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

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Peran</label>
                <select name="role" class="form-select" required>
                    <option value="staff" @selected(old('role', 'staff') === 'staff')>Staff</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
                <small class="text-muted">Admin dapat mengelola user. Staff hanya mengelola data.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
                <small class="text-muted">Minimal 8 karakter</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Ulangi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary-custom">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
@endsection