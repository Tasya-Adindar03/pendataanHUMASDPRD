<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // KPI
        $totalData = Advertorial::count();
        $totalNominal = Advertorial::sum('nominal');
        $totalSudahDibayar = Advertorial::where('status', 'sudah_dibayar')->sum('nominal');
        $totalBelumDibayar = Advertorial::whereIn('status', ['belum_dibayar', 'jatuh_tempo'])->sum('nominal');
        $jumlahJatuhTempo = Advertorial::where('status', 'jatuh_tempo')->count();

        // Chart status
        $statusData = [
            'sudah_dibayar' => Advertorial::where('status', 'sudah_dibayar')->count(),
            'belum_dibayar' => Advertorial::where('status', 'belum_dibayar')->count(),
            'jatuh_tempo' => Advertorial::where('status', 'jatuh_tempo')->count(),
        ];

        // Chart cetak vs online
        $tipeData = [
            'cetak' => Advertorial::cetak()->sum('nominal'),
            'online' => Advertorial::online()->sum('nominal'),
        ];

        // Chart tren 6 bulan
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

        // Top 5 perusahaan
        $topCompanies = Advertorial::selectRaw('company_id, SUM(nominal) as total')
            ->with('company')
            ->groupBy('company_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Planning bulan ini
        $planBulanIni = AdvertisingPlan::with(['company', 'media'])
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->get();

        // ===== Notifikasi tenggat =====
        $jumlahTerlambat = Advertorial::where('status', 'jatuh_tempo')->count();
        $nominalTerlambat = Advertorial::where('status', 'jatuh_tempo')->sum('nominal');

        $daftarTerlambat = Advertorial::with(['company', 'media'])
            ->where('status', 'jatuh_tempo')
            ->orderBy('tanggal_jatuh_tempo')
            ->limit(5)
            ->get();

        $segeraQuery = Advertorial::where('status', 'belum_dibayar')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereBetween('tanggal_jatuh_tempo', [today()->toDateString(), today()->addDays(7)->toDateString()]);

        $jumlahSegera = (clone $segeraQuery)->count();
        $nominalSegera = (clone $segeraQuery)->sum('nominal');

        $daftarSegera = (clone $segeraQuery)
            ->with(['company', 'media'])
            ->orderBy('tanggal_jatuh_tempo')
            ->limit(5)
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
            'planBulanIni',
            'jumlahTerlambat',
            'nominalTerlambat',
            'daftarTerlambat',
            'jumlahSegera',
            'nominalSegera',
            'daftarSegera'
        ));
    }
}