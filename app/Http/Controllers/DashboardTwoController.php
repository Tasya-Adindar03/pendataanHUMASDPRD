<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardTwoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['bulan' => ['nullable', 'date_format:Y-m']]);
        $month = Carbon::createFromFormat('!Y-m', $request->input('bulan') ?: now()->format('Y-m'))->locale('id');
        $end = $month->copy()->endOfMonth();
        $today = now()->startOfDay();
        $rows = Advertorial::with(['company', 'media'])->whereBetween('tanggal_kwitansi', [$month, $end])->get();
        $total = (float) $rows->sum('nominal');
        $paid = (float) $rows->where('status', 'sudah_dibayar')->sum('nominal');
        $unpaid = $rows->whereIn('status', ['belum_dibayar', 'jatuh_tempo']);
        $outstanding = (float) $unpaid->sum('nominal');
        $overdueRows = $unpaid->filter(fn ($row) => $row->tanggal_jatuh_tempo && $row->tanggal_jatuh_tempo->lt($today));
        $overdue = (float) $overdueRows->sum('nominal');
        $previous = $month->copy()->subMonth();
        $previousTotal = (float) Advertorial::whereBetween('tanggal_kwitansi', [$previous, $previous->copy()->endOfMonth()])->sum('nominal');
        $growth = $previousTotal > 0 ? round(($total - $previousTotal) / $previousTotal * 100, 1) : null;
        $paidRate = $total > 0 ? round($paid / $total * 100, 1) : 0;
        $statusKeys = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'];
        $statusValues = array_map(fn ($status) => (float) $rows->where('status', $status)->sum('nominal'), $statusKeys);

        $daily = [];
        $cumulative = 0;
        $cumulativePaid = 0;
        for ($day = 1; $day <= $month->daysInMonth; $day++) {
            $date = $month->copy()->day($day);
            $group = $rows->filter(fn ($row) => $row->tanggal_kwitansi->isSameDay($date));
            $value = (float) $group->sum('nominal');
            $cumulative += $value;
            $cumulativePaid += (float) $group->where('status', 'sudah_dibayar')->sum('nominal');
            $daily[] = ['day' => $day, 'date' => $date->toDateString(), 'count' => $group->count(), 'value' => $value, 'cumulative' => $cumulative, 'paid' => $cumulativePaid];
        }
        $companies = $rows->groupBy('company_id')->map(fn ($group) => [
            'id' => $group->first()->company_id,
            'name' => $group->first()->company->nama_perusahaan ?? 'Perusahaan dihapus',
            'total' => (float) $group->sum('nominal'),
            'paid' => (float) $group->where('status', 'sudah_dibayar')->sum('nominal'),
            'outstanding' => (float) $group->whereIn('status', ['belum_dibayar', 'jatuh_tempo'])->sum('nominal'),
        ])->sortByDesc('total')->values();
        $debtor = $companies->sortByDesc('outstanding')->first();
        $media = $rows->groupBy('media_id')->map(fn ($group) => [
            'id' => $group->first()->media_id,
            'name' => $group->first()->media->nama_media ?? 'Media dihapus',
            'type' => $group->first()->media->tipe ?? 'lainnya',
            'count' => $group->count(),
            'total' => (float) $group->sum('nominal'),
            'average' => (float) $group->avg('nominal'),
        ])->values()->all();
        $typeValues = array_map(fn ($type) => (float) $rows->filter(fn ($row) => optional($row->media)->tipe === $type)->sum('nominal'), ['cetak', 'online']);
        $aging = [0, 0, 0, 0, 0];
        foreach ($unpaid as $row) {
            $due = $row->tanggal_jatuh_tempo;
            $days = $due ? $due->diffInDays($today, false) : null;
            $bucket = $days === null ? 4 : ($days <= 0 ? 0 : ($days <= 30 ? 1 : ($days <= 60 ? 2 : 3)));
            $aging[$bucket] += (float) $row->nominal;
        }
        $plans = AdvertisingPlan::where('bulan', $month->month)->where('tahun', $month->year)->withCount('advertorials')->get();
        $quota = $plans->sum('kuota');
        $realized = $plans->sum(fn ($plan) => min($plan->kuota, $plan->advertorials_count));
        $planRate = $quota > 0 ? round($realized / $quota * 100, 1) : 0;
        $planDone = $plans->filter(fn ($plan) => $plan->advertorials_count >= $plan->kuota)->count();
        $count = $rows->count();
        $peak = collect($daily)->sortByDesc('value')->first();
        $chartData = [
            'daily' => $daily, 'status' => $statusValues, 'companies' => $companies->take(5)->values()->all(),
            'media' => $media, 'type' => $typeValues, 'aging' => $aging,
            'total' => $total, 'paid' => $paid, 'outstanding' => $outstanding,
            'quota' => $quota, 'realized' => $realized,
        ];

        return view('dashboard.two', compact(
            'month', 'end', 'total', 'paid', 'outstanding', 'overdue', 'overdueRows',
            'growth', 'previousTotal', 'paidRate', 'daily', 'debtor', 'count', 'peak',
            'quota', 'realized', 'planRate', 'planDone', 'plans', 'chartData'
        ));
    }
}
