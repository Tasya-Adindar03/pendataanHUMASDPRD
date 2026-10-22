<div class="table-responsive">
    <table class="table table-custom align-middle">
        <thead>
            <tr>
                <th>No Invoice</th>
                <th>Planning</th>
                <th>Perusahaan</th>
                <th>Media</th>
                <th>Judul</th>
                <th>Waktu Tayang</th>
                <th>Tgl Kwitansi</th>
                <th>Tenggat</th>
                <th>Tgl Dibayar</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($advertorials as $item)
                <tr>
                    <td class="fw-semibold">{{ $item->no_invoice }}</td>
                    <td>
                        @if ($item->plan)
                            <span class="badge-status badge-sudah">{{ $item->plan->nama_plan }}</span>
                        @else
                            <span class="badge-status badge-belum">Belum Ada Planning</span>
                        @endif
                    </td>
                    <td>{{ $item->company->nama_perusahaan }}</td>
                    <td>{{ $item->media->nama_media }}</td>
                    <td>{{ $item->judul }}</td>
                    <td>{{ $item->waktu_tayang->format('d M Y') }}</td>
                    <td>{{ $item->tanggal_kwitansi->format('d M Y') }}</td>
                    <td>
                        @if ($item->tanggal_jatuh_tempo)
                            {{ $item->tanggal_jatuh_tempo->format('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->tanggal_dibayar?->format('d M Y') ?? '-' }}</td>
                    <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    <td>
                        @if ($item->status === 'sudah_dibayar')
                            <span class="badge-status badge-sudah">Sudah Dibayar</span>
                        @elseif ($item->status === 'jatuh_tempo')
                            <span class="badge-status badge-jatuh">Jatuh Tempo</span>
                        @else
                            <span class="badge-status badge-belum">Belum Dibayar</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('advertorials.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('advertorials.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
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
                    <td colspan="11" class="text-center text-muted py-4">Belum ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $advertorials->links() }}