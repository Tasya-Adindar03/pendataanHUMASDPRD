<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use Carbon\Carbon;
use App\Support\MonthRange;
use Illuminate\Http\Request;

class DashboardTwoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
            'tipe' => ['nullable', 'in:cetak,online'],
            'status' => ['nullable', 'in:sudah_dibayar,belum_dibayar,jatuh_tempo'],
            'company_id' => ['nullable', 'integer', 'min:1'],
            'media_id' => ['nullable', 'integer', 'min:1'],
            'hari' => ['nullable', 'integer', 'between:1,31'],
            'aging' => ['nullable', 'integer', 'between:0,4'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'periode' => ['nullable', 'date_format:Y-m'],
        ]);
        [$month, $end] = MonthRange::resolve($request);
        $monthCount = $month->diffInMonths($end->copy()->startOfMonth()) + 1;
        $periodLabel = $monthCount === 1 ? $month->translatedFormat('F Y') : $month->translatedFormat('M Y').' – '.$end->translatedFormat('M Y');
        $today = now()->startOfDay();
        $query = Advertorial::with(['company', 'media'])->whereBetween('tanggal_kwitansi', [$month, $end]);
        $apply = function ($query) use ($request) {
            foreach (['company_id', 'media_id', 'status'] as $field) {
                if ($request->filled($field)) { $query->where($field, $request->input($field)); }
            }
            if ($request->filled('tipe')) { $query->whereHas('media', fn ($q) => $q->where('tipe', $request->input('tipe'))); }
        };
        $apply($query);
        if ($request->filled('hari')) { $query->whereDay('tanggal_kwitansi', $request->integer('hari')); }
        if ($request->filled('tanggal')) { $query->whereDate('tanggal_kwitansi', $request->input('tanggal')); }
        if ($request->filled('periode')) {
            $focusMonth = Carbon::createFromFormat('!Y-m', $request->input('periode'));
            $query->whereBetween('tanggal_kwitansi', [$focusMonth, $focusMonth->copy()->endOfMonth()]);
        }
        $rows = $query->get();
        $ageBucket = function ($row) use ($today) {
            if ($row->status === 'sudah_dibayar') { return null; }
            $due = $row->tanggal_jatuh_tempo;
            $days = $due ? $due->diffInDays($today, false) : null;
            return $days === null ? 4 : ($days <= 0 ? 0 : ($days <= 30 ? 1 : ($days <= 60 ? 2 : 3)));
        };
        if ($request->filled('aging')) { $rows = $rows->filter(fn ($row) => $ageBucket($row) === $request->integer('aging')); }
        $total = (float) $rows->sum('nominal');
        $paid = (float) $rows->where('status', 'sudah_dibayar')->sum('nominal');
        $unpaid = $rows->whereIn('status', ['belum_dibayar', 'jatuh_tempo']);
        $outstanding = (float) $unpaid->sum('nominal');
        $overdueRows = $unpaid->filter(fn ($row) => $row->tanggal_jatuh_tempo && $row->tanggal_jatuh_tempo->lt($today));
        $overdue = (float) $overdueRows->sum('nominal');
        $previous = $month->copy()->subMonths($monthCount);
        $previousQuery = Advertorial::whereBetween('tanggal_kwitansi', [$previous, $month->copy()->subDay()->endOfDay()]);
        $apply($previousQuery);
        $previousTotal = $request->filled('hari') || $request->filled('aging') || $request->filled('tanggal') || $request->filled('periode') ? 0 : (float) $previousQuery->sum('nominal');
        $growth = $previousTotal > 0 ? round(($total - $previousTotal) / $previousTotal * 100, 1) : null;
        $paidRate = $total > 0 ? round($paid / $total * 100, 1) : 0;
        $statusKeys = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'];
        $statusValues = array_map(fn ($status) => (float) $rows->where('status', $status)->sum('nominal'), $statusKeys);

        $daily = [];
        $cumulative = 0;
        $cumulativePaid = 0;
        for ($date = $month->copy(); $date->lte($end); $date->addDay()) {
            $day = $date->day;
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
            'outstanding' => (float) $group->whereIn('status', ['belum_dibayar', 'jatuh_tempo'])->sum('nominal'),
        ])->values()->all();
        $typeValues = array_map(fn ($type) => (float) $rows->filter(fn ($row) => optional($row->media)->tipe === $type)->sum('nominal'), ['cetak', 'online']);
        $aging = [0, 0, 0, 0, 0];
        foreach ($unpaid as $row) {
            $bucket = $ageBucket($row);
            $aging[$bucket] += (float) $row->nominal;
        }
        $planQuery = AdvertisingPlan::withCount('advertorials')->where(function ($query) use ($month, $monthCount) {
            for ($i = 0; $i < $monthCount; $i++) {
                $periodMonth = $month->copy()->addMonths($i);
                $query->orWhere(fn ($q) => $q->where('bulan', $periodMonth->month)->where('tahun', $periodMonth->year));
            }
        });
        foreach (['company_id', 'media_id'] as $field) {
            if ($request->filled($field)) { $planQuery->where($field, $request->input($field)); }
        }
        if ($request->filled('tipe')) { $planQuery->whereHas('media', fn ($q) => $q->where('tipe', $request->input('tipe'))); }
        $plans = $planQuery->get();
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
            'quota' => $quota, 'realized' => $realized, 'monthCount' => $monthCount,
            'typeProfiles' => array_map(function ($type) use ($rows) {
                $group = $rows->filter(fn ($row) => optional($row->media)->tipe === $type);
                return ['count' => $group->count(), 'total' => (float) $group->sum('nominal'), 'paid' => (float) $group->where('status', 'sudah_dibayar')->sum('nominal')];
            }, ['cetak', 'online']),
        ];

        $focus = $request->only(['tipe', 'status', 'company_id', 'media_id', 'hari', 'aging', 'tanggal', 'periode']);
        $focusNames = [];
        foreach ($focus as $key => $value) {
            if ($value === null || $value === '') { continue; }
            $focusNames[$key] = match ($key) {
                'tipe' => 'Media '.ucfirst($value),
                'status' => ['sudah_dibayar' => 'Lunas', 'belum_dibayar' => 'Belum dibayar', 'jatuh_tempo' => 'Status jatuh tempo'][$value],
                'hari' => 'Hari ke-'.$value,
                'tanggal' => 'Tanggal '.$value,
                'periode' => 'Fokus bulan '.$value,
                'aging' => ['Belum lewat tenggat', 'Lewat 1–30 hari', 'Lewat 31–60 hari', 'Lewat >60 hari', 'Tanpa tenggat'][(int) $value],
                'company_id' => 'Perusahaan #'.$value,
                'media_id' => 'Media #'.$value,
            };
        }
        $detailRows = $rows->sortByDesc('nominal')->take(50);
        $monthTiles = collect($daily)->groupBy(fn ($day) => substr($day['date'], 0, 7))->map(fn ($days, $key) => [
            'key' => $key, 'label' => Carbon::createFromFormat('!Y-m', $key)->locale('id')->translatedFormat('M Y'),
            'value' => $days->sum('value'), 'count' => $days->sum('count'),
        ])->values()->all();
        return view('dashboard.two', compact(
            'focus', 'focusNames', 'detailRows', 'monthCount', 'periodLabel', 'monthTiles',
            'month', 'end', 'total', 'paid', 'outstanding', 'overdue', 'overdueRows',
            'growth', 'previousTotal', 'paidRate', 'daily', 'debtor', 'count', 'peak',
            'quota', 'realized', 'planRate', 'planDone', 'plans', 'chartData'
        ));
    }
}
