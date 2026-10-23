<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->locale('id')->startOfDay();
        $start = $today->copy()->startOfMonth()->subMonths(5);
        $end = $today->copy()->endOfMonth();
        $records = Advertorial::with(['company', 'media'])
            ->whereBetween('tanggal_kwitansi', [$start, $end])->get();
        $totalData = $records->count();
        $totalNominal = (float) $records->sum('nominal');
        $paid = $records->where('status', 'sudah_dibayar');
        $unpaid = $records->whereIn('status', ['belum_dibayar', 'jatuh_tempo']);
        $totalSudahDibayar = (float) $paid->sum('nominal');
        $totalBelumDibayar = (float) $unpaid->sum('nominal');
        $paidRate = $totalNominal > 0 ? round($totalSudahDibayar / $totalNominal * 100, 1) : 0;
        $overdue = $unpaid->filter(fn ($row) => $row->tanggal_jatuh_tempo && $row->tanggal_jatuh_tempo->lt($today));
        $overdueTotal = (float) $overdue->sum('nominal');

        $statusKeys = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'];
        $statusCounts = array_map(fn ($status) => $records->where('status', $status)->count(), $statusKeys);
        $monthly = [];
        $paymentRecords = Advertorial::where('status', 'sudah_dibayar')
            ->whereBetween('tanggal_dibayar', [$start, $end])->get();
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $rows = $records->filter(fn ($row) => $row->tanggal_kwitansi->format('Y-m') === $key);
            $monthly[] = [
                'label' => $month->translatedFormat('M Y'),
                'nominal' => (float) $rows->sum('nominal'),
                'count' => $rows->count(),
                'statuses' => array_map(fn ($status) => $rows->where('status', $status)->count(), $statusKeys),
                'paid' => (float) $paymentRecords->filter(fn ($row) => $row->tanggal_dibayar->format('Y-m') === $key)->sum('nominal'),
                'from' => $month->format('Y-m-d'),
                'to' => $month->copy()->endOfMonth()->format('Y-m-d'),
            ];
        }

        $rank = fn ($relation) => $records->groupBy($relation.'_id')->map(fn ($rows) => [
            'id' => $rows->first()->{$relation.'_id'},
            'label' => $relation === 'company'
                ? ($rows->first()->company->nama_perusahaan ?? 'Perusahaan dihapus')
                : ($rows->first()->media->nama_media ?? 'Media dihapus'),
            'total' => (float) $rows->sum('nominal'),
        ])->sortByDesc('total')->take(5)->values()->all();
        $topCompanies = $rank('company');
        $topMedia = $rank('media');
        $typeValues = array_map(fn ($type) => (float) $records->filter(fn ($row) => optional($row->media)->tipe === $type)->sum('nominal'), ['cetak', 'online']);

        $aging = [0, 0, 0, 0, 0];
        foreach ($unpaid as $row) {
            if (!$row->tanggal_jatuh_tempo) {
                $bucket = 4;
            } elseif ($row->tanggal_jatuh_tempo->gte($today)) {
                $bucket = 0;
            } else {
                $days = $row->tanggal_jatuh_tempo->diffInDays($today);
                $bucket = $days <= 30 ? 1 : ($days <= 60 ? 2 : 3);
            }
            $aging[$bucket] += (float) $row->nominal;
        }

        $plans = AdvertisingPlan::with(['company', 'media'])->withCount('advertorials')
            ->where('bulan', $today->month)->where('tahun', $today->year)->get();
        $planCounts = [
            $plans->filter(fn ($plan) => $plan->advertorials_count >= $plan->kuota)->count(),
            $plans->filter(fn ($plan) => $plan->advertorials_count > 0 && $plan->advertorials_count < $plan->kuota)->count(),
            $plans->filter(fn ($plan) => $plan->advertorials_count === 0 && $plan->kuota > 0)->count(),
        ];
        $quota = $plans->sum('kuota');
        $realized = $plans->sum(fn ($plan) => min($plan->advertorials_count, $plan->kuota));
        $planRate = $quota > 0 ? round($realized / $quota * 100, 1) : 0;
        $last = $monthly[5]['nominal'];
        $previous = $monthly[4]['nominal'];
        $growth = $previous > 0 ? round(($last - $previous) / $previous * 100, 1) : null;
        $charts = compact('monthly', 'statusCounts', 'typeValues', 'topCompanies', 'topMedia', 'aging', 'planCounts');
        $period = $start->translatedFormat('M Y').' – '.$end->translatedFormat('M Y');

        return view('dashboard.index', compact(
            'charts', 'period', 'totalData', 'totalNominal', 'totalSudahDibayar',
            'totalBelumDibayar', 'paidRate', 'overdueTotal', 'overdue', 'growth',
            'plans', 'planRate', 'realized', 'quota', 'start', 'end'
        ));
    }
}
