@extends('layouts.app')
@section('title', 'Kelola Media')

@section('content')
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama media..." value="{{ request('search') }}">
                <select name="tipe" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="cetak" @selected(request('tipe') === 'cetak')>Cetak</option>
                    <option value="online" @selected(request('tipe') === 'online')>Online</option>
                </select>
                <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <a href="{{ route('media.create') }}" class="btn btn-primary-custom btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Media
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th>Nama Media</th>
                        <th>Tipe</th>
                        <th>Rate Card</th>
                        <th>Kontak PIC</th>
                        <th>Jumlah Data</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mediaList as $media)
                        <tr>
                            <td class="fw-semibold">{{ $media->nama_media }}</td>
                            <td>
                                @if ($media->tipe === 'cetak')
                                    <span class="badge-status badge-belum"><i class="bi bi-file-earmark-text"></i> Cetak</span>
                                @else
                                    <span class="badge-status badge-sudah"><i class="bi bi-globe"></i> Online</span>
                                @endif
                            </td>
                            <td>{{ $media->rate_card ? 'Rp ' . number_format($media->rate_card, 0, ',', '.') : '-' }}</td>
                            <td>{{ $media->pic_kontak ?? '-' }}</td>
                            <td>{{ $media->advertorials()->count() }}</td>
                            <td>
                                <a href="{{ route('media.edit', $media) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('media.destroy', $media) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus media ini?')">
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
                            <td colspan="6" class="text-center text-muted py-4">Belum ada media terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $mediaList->links() }}
    </div>
@endsection