<?php

namespace App\Http\Controllers;

use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use Illuminate\Http\Request;

class AdvertisingPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = AdvertisingPlan::with(['company', 'media']);

        if ($request->filled('bulan')) {
            $query->where('bulan', (int) $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', (int) $request->tahun);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('media_id')) {
            $query->where('media_id', $request->media_id);
        }

        $plans = $query->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get()
            ->groupBy(fn ($p) => $p->bulan . '-' . $p->tahun);

        $companies = Company::orderBy('nama_perusahaan')->get();
        $mediaList = Media::orderBy('nama_media')->get();

        return view('plans.index', compact('plans', 'companies', 'mediaList'));
    }

    public function create()
    {
        $companies = Company::orderBy('nama_perusahaan')->get();
        $mediaList = Media::orderBy('nama_media')->get();

        return view('plans.create', compact('companies', 'mediaList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'media_id' => 'required|exists:media,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kuota' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        $duplikat = AdvertisingPlan::where('company_id', $validated['company_id'])
            ->where('media_id', $validated['media_id'])
            ->where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun'])
            ->exists();

        if ($duplikat) {
            return back()->withInput()->withErrors([
                'kuota' => 'Planning untuk kombinasi perusahaan, media, dan bulan ini sudah ada. Silakan edit yang sudah ada.',
            ]);
        }

        AdvertisingPlan::create($validated);

        return redirect()->route('plans.index', ['bulan' => $validated['bulan'], 'tahun' => $validated['tahun']])
            ->with('success', 'Planning iklan berhasil ditambahkan.');
    }

    public function edit(AdvertisingPlan $plan)
    {
        $companies = Company::orderBy('nama_perusahaan')->get();
        $mediaList = Media::orderBy('nama_media')->get();

        return view('plans.edit', compact('plan', 'companies', 'mediaList'));
    }

    public function update(Request $request, AdvertisingPlan $plan)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'media_id' => 'required|exists:media,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kuota' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        $duplikat = AdvertisingPlan::where('company_id', $validated['company_id'])
            ->where('media_id', $validated['media_id'])
            ->where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun'])
            ->where('id', '!=', $plan->id)
            ->exists();

        if ($duplikat) {
            return back()->withInput()->withErrors([
                'kuota' => 'Planning untuk kombinasi ini sudah ada.',
            ]);
        }

        $plan->update($validated);

        return redirect()->route('plans.index', ['bulan' => $validated['bulan'], 'tahun' => $validated['tahun']])
            ->with('success', 'Planning iklan berhasil diperbarui.');
    }

    public function destroy(AdvertisingPlan $plan)
    {
        $plan->delete();

        return back()->with('success', 'Planning iklan berhasil dihapus.');
    }
}