<?php

namespace App\Http\Controllers;

use App\Models\AdvertisingPlan;
use App\Models\Media;
use Illuminate\Http\Request;

class AdvertisingPlanController extends Controller
{
    // Tambah kuota untuk satu media (dari halaman Kelola Media -> Kuota)
    public function store(Request $request, Media $medium)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kuota' => 'required|integer|min:1',
        ]);

        $existing = AdvertisingPlan::withTrashed()
            ->where('company_id', $validated['company_id'])
            ->where('media_id', $medium->id)
            ->where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun'])
            ->first();

        if ($existing && !$existing->trashed()) {
            return back()->withInput()->with('error', 'Kuota untuk perusahaan dan bulan ini sudah ada. Ubah angkanya langsung di tabel.');
        }

        if ($existing) {
            $existing->restore();
            $existing->update(['kuota' => $validated['kuota']]);
        } else {
            AdvertisingPlan::create($validated + ['media_id' => $medium->id]);
        }

        return back()->with('success', 'Kuota berhasil ditambahkan.');
    }

    // Ubah total kuota
    public function update(Request $request, AdvertisingPlan $plan)
    {
        $validated = $request->validate([
            'kuota' => 'required|integer|min:1',
        ]);

        $plan->update($validated);

        return back()->with('success', 'Kuota berhasil diperbarui.');
    }

    public function destroy(AdvertisingPlan $plan)
    {
        $plan->delete();

        return back()->with('success', 'Kuota berhasil dihapus.');
    }
}