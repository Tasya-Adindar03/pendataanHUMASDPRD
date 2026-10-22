<?php

namespace App\Http\Controllers;

use App\Exports\AdvertorialExport;
use App\Models\Advertorial;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function pdf(Request $request)
    {
        $advertorials = $this->getFilteredData($request);

        $grouped = $advertorials->groupBy(fn ($item) => $item->company->nama_perusahaan ?? 'Tanpa Perusahaan');

        $totalNominal = $advertorials->sum('nominal');
        $judulLaporan = $this->buildJudulLaporan($request);

        $pdf = Pdf::loadView('exports.pdf', compact('grouped', 'totalNominal', 'judulLaporan'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-advertorial-' . now()->format('Ymd-His') . '.pdf');
    }

    public function excel(Request $request)
    {
        $advertorials = $this->getFilteredData($request);

        $namaFile = 'laporan-advertorial-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new AdvertorialExport($advertorials), $namaFile);
    }

    private function getFilteredData(Request $request)
    {
        $query = Advertorial::with(['company', 'media']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('media_id')) {
            $query->where('media_id', $request->media_id);
        }

        if ($request->filled('tipe')) {
            $query->whereHas('media', fn ($q) => $q->where('tipe', $request->tipe));
        }

        if ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal_kwitansi', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal_kwitansi', '<=', $request->sampai_tanggal);
        }

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        return $query->orderBy('company_id')->orderBy('tanggal_kwitansi')->get();
    }

    private function buildJudulLaporan(Request $request): string
    {
        $bagian = ['Laporan Data Advertorial'];

        if ($request->filled('status')) {
            $bagian[] = match ($request->status) {
                'sudah_dibayar' => '- Sudah Dibayar',
                'belum_dibayar' => '- Belum Dibayar',
                'jatuh_tempo' => '- Jatuh Tempo',
                default => '',
            };
        } else {
            $bagian[] = '- Semua Status';
        }

        if ($request->filled('tipe')) {
            $bagian[] = '(' . ucfirst($request->tipe) . ')';
        }

        return implode(' ', array_filter($bagian));
    }
}