<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalData = Advertorial::count();
        $totalNominal = Advertorial::sum('nominal');
        $totalSudahDibayar = Advertorial::where('status', 'sudah_dibayar')->sum('nominal');
        $totalBelumDibayar = Advertorial::whereIn('status', ['belum_dibayar', 'jatuh_tempo'])->sum('nominal');
        $jumlahJatuhTempo = Advertorial::where('status', 'jatuh_tempo')->count();

        $statusData = [
            'sudah_dibayar' => Advertorial::where('status', 'sudah_dibayar')->count(),
            'belum_dibayar' => Advertorial::where('status', 'belum_dibayar')->count(),
            'jatuh_tempo' => Advertorial::where('status', 'jatuh_tempo')->count(),
        ];

        $tipeData = [
            'cetak' => Advertorial::cetak()->sum('nominal'),
            'online' => Advertorial::online()->sum('nominal'),
        ];

        $bulanLabels = [];
        $bulanNominal = [];
        $bulanRanges = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $bulanLabels[] = $bulan->translatedFormat('M Y');
            $bulanNominal[] = Advertorial::whereYear('tanggal_kwitansi', $bulan->year)
                ->whereMonth('tanggal_kwitansi', $bulan->month)
                ->sum('nominal');
            $bulanRanges[] = [
                'dari' => $bulan->copy()->startOfMonth()->format('Y-m-d'),
                'sampai' => $bulan->copy()->endOfMonth()->format('Y-m-d'),
            ];
        }

        $topCompanies = Advertorial::selectRaw('company_id, SUM(nominal) as total')
            ->with('company')
            ->groupBy('company_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $planBulanIni = AdvertisingPlan::with(['company', 'media'])
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->get();

        return view('dashboard.index', compact(
            'totalData',
            'totalNominal',
            'totalSudahDibayar',
            'totalBelumDibayar',
            'jumlahJatuhTempo',
            'statusData',
            'tipeData',
            'bulanLabels',
            'bulanNominal',
            'bulanRanges',
            'topCompanies',
            'planBulanIni'
        ));
    }
}