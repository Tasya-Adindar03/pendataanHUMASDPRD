@if (request('search'))
    <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
        <span class="badge bg-light text-dark border py-2 px-3">
            <i class="bi bi-search"></i> Hasil pencarian: <strong>{{ request('search') }}</strong>
        </span>
        <a href="{{ route('advertorials.index', request()->except(['search', 'page'])) }}" class="small">Hapus pencarian</a>
    </div>
@endif

<div class="table-responsive">
    <table class="table table-custom table-stack align-middle">
        <thead>
            <tr>
                <th>Perusahaan &amp; Media</th>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Pembayaran</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($advertorials as $item)
                @php $umur = $item->umur_piutang; @endphp
                <tr class="{{ $item->status === 'jatuh_tempo' ? 'row-overdue' : '' }}">
                    <td data-label="Perusahaan">
                        <div class="cell">
                            <div class="fw-semibold">{{ $item->company->nama_perusahaan ?? '-' }}</div>
                            <div class="small text-muted">
                                {{ $item->media->nama_media ?? '-' }}
                                <span class="badge bg-light text-dark border">{{ ucfirst($item->media->tipe ?? '') }}</span>
                            </div>
                        </div>
                    </td>

                    <td data-label="Judul" style="min-width: 200px;">
                        <div class="cell">
                            <div>{{ $item->judul }}</div>
                            <div class="small text-muted">
                                {{ $item->no_invoice }}
                                @if ($item->plan)
                                    &middot; Kuota {{ $item->plan->label_periode }}
                                @endif
                            </div>
                        </div>
                    </td>

                    <td data-label="Tanggal" class="small text-nowrap">
                        <div class="cell">
                            <div><span class="text-muted">Tayang</span> {{ $item->waktu_tayang->format('d M Y') }}</div>
                            <div><span class="text-muted">Kwitansi</span> {{ $item->tanggal_kwitansi->format('d M Y') }}</div>
                        </div>
                    </td>

                    <td data-label="Pembayaran" class="text-nowrap">
                        <div class="cell">
                            @if ($item->status === 'sudah_dibayar')
                                <div class="small fw-semibold text-success"><i class="bi bi-check-circle-fill"></i> Dibayar</div>
                                <div class="small text-muted">{{ $item->tanggal_dibayar?->format('d M Y') ?? '-' }}</div>
                            @elseif ($item->tanggal_jatuh_tempo)
                                <div class="small">Tenggat {{ $item->tanggal_jatuh_tempo->format('d M Y') }}</div>
                                @if ($umur < 0)
                                    <div class="small text-danger fw-semibold">Terlambat {{ abs($umur) }} hari</div>
                                @elseif ($umur === 0)
                                    <div class="small fw-semibold" style="color: #b45309;">Jatuh tempo hari ini</div>
                                @else
                                    <div class="small text-muted">{{ $umur }} hari lagi</div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </td>

                    <td data-label="Nominal" class="fw-semibold text-nowrap">
                        <div class="cell">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                    </td>

                    <td data-label="Status">
                        <div class="cell">
                            @if ($item->status === 'sudah_dibayar')
                                <span class="badge-status badge-sudah">Sudah Dibayar</span>
                            @elseif ($item->status === 'jatuh_tempo')
                                <span class="badge-status badge-jatuh">Jatuh Tempo</span>
                            @else
                                <span class="badge-status badge-belum">Belum Dibayar</span>
                            @endif
                        </div>
                    </td>

                    <td data-label="Aksi" class="text-nowrap">
                        <div class="cell">
                            @if ($item->file_kwitansi)
                                <a href="{{ asset('storage/' . $item->file_kwitansi) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat kwitansi">
                                    <i class="bi bi-paperclip"></i>
                                </a>
                            @endif
                            <a href="{{ route('advertorials.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('advertorials.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
                        <div class="text-muted mt-2 mb-3">Belum ada data yang sesuai.</div>
                        <a href="{{ route('advertorials.create') }}" class="btn btn-sm btn-primary-custom">
                            <i class="bi bi-plus-lg"></i> Tambah Data
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $advertorials->links('pagination::bootstrap-5') }}