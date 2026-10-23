@extends('layouts.app')
@section('title', 'Kelola Perusahaan')

@section('content')
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama perusahaan..." value="{{ request('search') }}">
                <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <a href="{{ route('companies.create') }}" class="btn btn-primary-custom btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Perusahaan
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th>Nama Perusahaan</th>
                        <th>PIC</th>
                        <th>Kontak PIC</th>
                        <th>Email</th>
                        <th>Jumlah Data</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td class="fw-semibold">{{ $company->nama_perusahaan }}</td>
                            <td>{{ $company->pic_nama ?? '-' }}</td>
                            <td>{{ $company->pic_kontak ?? '-' }}</td>
                            <td>{{ $company->email ?? '-' }}</td>
                            <td>{{ $company->advertorials()->count() }}</td>
                            <td>
                                <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus perusahaan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada perusahaan terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $companies->onEachSide(1)->links() }}
    </div>
@endsection