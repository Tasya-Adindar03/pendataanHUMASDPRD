<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1a202c; }
        h2 { margin-bottom: 2px; }
        .subtitle { color: #718096; margin-bottom: 20px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #cbd5e0; padding: 5px 7px; text-align: left; }
        th { background-color: #1e3a5f; color: #fff; font-size: 10px; text-transform: uppercase; }
        .company-header { background-color: #edf2f7; font-weight: bold; padding: 6px; margin-top: 10px; }
        .text-right { text-align: right; }
        .subtotal-row td { font-weight: bold; background-color: #f7fafc; }
        .grand-total { margin-top: 15px; font-size: 13px; font-weight: bold; text-align: right; }
        .badge-sudah { color: #38a169; font-weight: bold; }
        .badge-belum { color: #b45309; font-weight: bold; }
        .badge-jatuh { color: #e53e3e; font-weight: bold; }
    </style>
</head>
<body>
    <h2>{{ $judulLaporan }}</h2>
    <div class="subtitle">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>

    @forelse ($grouped as $namaPerusahaan => $items)
        <div class="company-header">{{ $namaPerusahaan }}</div>
        <table>
            <thead>
                <tr>
                    <th>No Invoice</th>
                    <th>Media</th>
                    <th>Judul</th>
                    <th>Waktu Tayang</th>
                    <th>Tgl Kwitansi</th>
                    <th>Tenggat</th>
                    <th>Tgl Dibayar</th>
                    <th class="text-right">Nominal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->no_invoice }}</td>
                        <td>{{ $item->media->nama_media ?? '-' }} ({{ ucfirst($item->media->tipe ?? '-') }})</td>
                        <td>{{ $item->judul }}</td>
                        <td>{{ $item->waktu_tayang?->format('d/m/Y') }}</td>
                        <td>{{ $item->tanggal_kwitansi?->format('d/m/Y') }}</td>
                        <td>{{ $item->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->tanggal_dibayar?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td>
                            @if ($item->status === 'sudah_dibayar')
                                <span class="badge-sudah">Sudah Dibayar</span>
                            @elseif ($item->status === 'jatuh_tempo')
                                <span class="badge-jatuh">Jatuh Tempo</span>
                            @else
                                <span class="badge-belum">Belum Dibayar</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="7">Subtotal {{ $namaPerusahaan }}</td>
                    <td class="text-right">Rp {{ number_format($items->sum('nominal'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @empty
        <p>Tidak ada data untuk laporan ini.</p>
    @endforelse

    <div class="grand-total">
        Total Keseluruhan: Rp {{ number_format($totalNominal, 0, ',', '.') }}
    </div>
</body>
</html>