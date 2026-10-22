<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdvertorialExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $advertorials;

    public function __construct($advertorials)
    {
        $this->advertorials = $advertorials;
    }

    public function collection()
    {
        return $this->advertorials;
    }

    public function headings(): array
    {
        return [
            'No Invoice',
            'Perusahaan',
            'Media',
            'Tipe Media',
            'Judul',
            'Waktu Tayang',
            'Tanggal Kwitansi',
            'Tenggat Pembayaran',
            'Tanggal Dibayar',
            'Nominal',
            'Status',
        ];
    }

    public function map($item): array
    {
        return [
            $item->no_invoice,
            $item->company->nama_perusahaan ?? '-',
            $item->media->nama_media ?? '-',
            ucfirst($item->media->tipe ?? '-'),
            $item->judul,
            $item->waktu_tayang?->format('d/m/Y'),
            $item->tanggal_kwitansi?->format('d/m/Y'),
            $item->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-',
            $item->tanggal_dibayar?->format('d/m/Y') ?? '-',
            (float) $item->nominal,
            match ($item->status) {
                'sudah_dibayar' => 'Sudah Dibayar',
                'jatuh_tempo' => 'Jatuh Tempo',
                default => 'Belum Dibayar',
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}