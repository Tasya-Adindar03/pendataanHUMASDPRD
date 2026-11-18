<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use Illuminate\Http\Request;

class AdvertisingPlanController extends Controller
{
    // Halaman Kelola Kuota Planning: semua perusahaan tampil, tinggal isi kuotanya
    public function index(Request $request)
    {
        $mediaList = Media::orderBy('tipe')->orderBy('nama_media')->get();

        $media = $mediaList->firstWhere('id', (int) $request->media_id) ?? $mediaList->first();
        $bulan = min(max((int) $request->get('bulan', now()->month), 1), 12);
        $tahun = min(max((int) $request->get('tahun', now()->year), 2020), 2100);

        $companies = Company::orderBy('nama_perusahaan')->get();

        $plans = collect();
        $terpakai = [];

        if ($media) {
            $plans = AdvertisingPlan::where('media_id', $media->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->get()
                ->keyBy('company_id');

            if ($plans->isNotEmpty()) {
                $terpakai = Advertorial::whereIn('plan_id', $plans->pluck('id')->all())
                    ->selectRaw('plan_id, COUNT(*) as total')
                    ->groupBy('plan_id')
                    ->pluck('total', 'plan_id')
                    ->all();
            }
        }

        $totalKuota = (int) $plans->sum('kuota');
        $totalTerpakai = (int) collect($terpakai)->sum();
        $totalSisa = (int) $plans->sum(fn ($p) => max($p->kuota - ($terpakai[$p->id] ?? 0), 0));

        $years = range(now()->year - 3, now()->year + 2);
        if (!in_array($tahun, $years)) {
            $years[] = $tahun;
            sort($years);
        }

        return view('kuota.index', compact(
            'mediaList', 'media', 'bulan', 'tahun', 'years',
            'companies', 'plans', 'terpakai',
            'totalKuota', 'totalTerpakai', 'totalSisa'
        ));
    }

    // Simpan semua perubahan kuota sekaligus
    public function store(Request $request)
    {
        $validated = $request->validate([
            'media_id' => 'required|exists:media,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kuota' => 'nullable|array',
            'kuota.*' => 'nullable|integer|min:0|max:100000',
        ], [
            'kuota.*.integer' => 'Kuota harus berupa angka bulat.',
            'kuota.*.min' => 'Kuota tidak boleh negatif.',
            'kuota.*.max' => 'Angka kuota terlalu besar.',
        ]);

        $validCompanyIds = Company::pluck('id')->all();
        $dibuat = $diubah = $dihapus = 0;

        foreach (($validated['kuota'] ?? []) as $companyId => $value) {
            $companyId = (int) $companyId;

            if (!in_array($companyId, $validCompanyIds, true)) {
                continue;
            }

            $value = ($value === null || $value === '') ? 0 : (int) $value;

            $plan = AdvertisingPlan::withTrashed()
                ->where('company_id', $companyId)
                ->where('media_id', $validated['media_id'])
                ->where('bulan', $validated['bulan'])
                ->where('tahun', $validated['tahun'])
                ->first();

            // Kuota 0 = tidak ada kuota
            if ($value === 0) {
                if ($plan && !$plan->trashed()) {
                    if ($plan->advertorials()->count() === 0) {
                        $plan->delete();
                        $dihapus++;
                    } elseif ((int) $plan->kuota !== 0) {
                        // sudah ada tayangan yang terhubung, jadi planning dipertahankan
                        $plan->update(['kuota' => 0]);
                        $diubah++;
                    }
                }
                continue;
            }

            if (!$plan) {
                AdvertisingPlan::create([
                    'company_id' => $companyId,
                    'media_id' => $validated['media_id'],
                    'bulan' => $validated['bulan'],
                    'tahun' => $validated['tahun'],
                    'kuota' => $value,
                ]);
                $dibuat++;
            } elseif ($plan->trashed()) {
                $plan->restore();
                $plan->update(['kuota' => $value]);
                $dibuat++;
            } elseif ((int) $plan->kuota !== $value) {
                $plan->update(['kuota' => $value]);
                $diubah++;
            }
        }

        $bagian = [];
        if ($dibuat) $bagian[] = $dibuat . ' baru';
        if ($diubah) $bagian[] = $diubah . ' diubah';
        if ($dihapus) $bagian[] = $dihapus . ' dihapus';

        $pesan = $bagian ? 'Kuota tersimpan: ' . implode(', ', $bagian) . '.' : 'Tidak ada perubahan kuota.';

        return redirect()
            ->route('kuota.index', [
                'media_id' => $validated['media_id'],
                'bulan' => $validated['bulan'],
                'tahun' => $validated['tahun'],
            ])
            ->with('success', $pesan);
    }
}