@extends('layouts.app')
@section('title', 'Edit User')

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

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Peran</label>
                @if ($user->id === auth()->id())
                    <input type="text" class="form-control" value="{{ $user->isAdmin() ? 'Admin' : 'Staff' }}" disabled>
                    <small class="text-muted">Peran akun yang sedang dipakai tidak bisa diubah.</small>
                @else
                    <select name="role" class="form-select">
                        <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                    </select>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti password</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Ulangi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary-custom">Update</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
@endsection