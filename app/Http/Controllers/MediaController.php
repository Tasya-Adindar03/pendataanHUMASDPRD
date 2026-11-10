<?php

namespace App\Http\Controllers;

use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::query();

        if ($request->filled('search')) {
            $query->where('nama_media', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $mediaList = $query->orderBy('nama_media')->paginate(15)->withQueryString();

        return view('media.index', compact('mediaList'));
    }

    // Halaman kuota per media (pengganti halaman Planning Iklan)
    public function show(Media $medium)
    {
        $plans = AdvertisingPlan::with('company')
            ->where('media_id', $medium->id)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $companies = Company::orderBy('nama_perusahaan')->get();

        return view('media.show', [
            'media' => $medium,
            'plans' => $plans,
            'companies' => $companies,
        ]);
    }

    public function create()
    {
        return view('media.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_media' => 'required|string|max:255',
            'tipe' => 'required|in:cetak,online',
            'rate_card' => 'nullable|numeric|min:0',
            'pic_kontak' => 'nullable|string|max:255',
        ]);

        Media::create($validated);

        return redirect()->route('media.index')->with('success', 'Media baru berhasil ditambahkan.');
    }

    public function edit(Media $medium)
    {
        return view('media.edit', ['media' => $medium]);
    }

    public function update(Request $request, Media $medium)
    {
        $validated = $request->validate([
            'nama_media' => 'required|string|max:255',
            'tipe' => 'required|in:cetak,online',
            'rate_card' => 'nullable|numeric|min:0',
            'pic_kontak' => 'nullable|string|max:255',
        ]);

        $medium->update($validated);

        return redirect()->route('media.index')->with('success', 'Data media berhasil diperbarui.');
    }

    public function destroy(Media $medium)
    {
        $medium->delete();

        return redirect()->route('media.index')->with('success', 'Media berhasil dihapus.');
    }
}