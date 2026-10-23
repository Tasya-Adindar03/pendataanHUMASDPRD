<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MonthRange
{
    public static function resolve(Request $request, int $defaultMonths = 1): array
    {
        $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
            'bulan_awal' => ['nullable', 'date_format:Y-m'],
            'bulan_akhir' => ['nullable', 'date_format:Y-m'],
        ]);
        $last = $request->input('bulan_akhir') ?: $request->input('bulan') ?: now()->format('Y-m');
        $end = Carbon::createFromFormat('!Y-m', $last)->locale('id')->endOfMonth();
        $first = $request->input('bulan_awal') ?: ($request->filled('bulan') ? $request->input('bulan') : $end->copy()->startOfMonth()->subMonths($defaultMonths - 1)->format('Y-m'));
        $start = Carbon::createFromFormat('!Y-m', $first)->locale('id');
        if ($start->gt($end) || $start->diffInMonths($end->copy()->startOfMonth()) >= 24) {
            throw ValidationException::withMessages(['bulan_akhir' => 'Bulan akhir harus setelah atau sama dengan bulan awal. Maksimal rentang 24 bulan.']);
        }
        return [$start, $end];
    }
}
