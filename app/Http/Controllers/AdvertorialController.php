<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use Illuminate\Http\Request;

class AdvertorialController extends Controller
{
    public function index(Request $request)
    {
        $query = Advertorial::with(['company', 'media', 'plan']);
        $this->applyFilters($query, $request);
        $advertorials = $query->latest('tanggal_kwitansi')->orderByDesc('id')->paginate(15)->withQueryString();

        $companies = Company::orderBy('nama_perusahaan')->get();
        $mediaList = Media::orderBy('nama_media')->get();

        return view('advertorials.index', compact('advertorials', 'companies', 'mediaList'));
    }

    // Halaman gabungan Media Cetak & Online (dengan tab)
    public function media(Request $request)
    {
        $tipe = in_array($request->get('tipe'), ['cetak', 'online']) ? $request->get('tipe') : 'cetak';

        $query = Advertorial::with(['company', 'media', 'plan'])
            ->whereHas('media', fn ($q) => $q->where('tipe', $tipe));
        $this->applyFilters($query, $request);
        $advertorials = $query->latest('tanggal_kwitansi')->orderByDesc('id')->paginate(15)->withQueryString();

        $companies = Company::orderBy('nama_perusahaan')->get();
        $mediaList = Media::where('tipe', $tipe)->orderBy('nama_media')->get();

        // Ringkasan kuota per perusahaan untuk tipe media ini, bulan berjalan
        $quotaSummary = AdvertisingPlan::with('company')
            ->whereHas('media', fn ($q) => $q->where('tipe', $tipe))
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->get()
            ->groupBy('company_id')
            ->map(function ($plans) {
                $kuota = $plans->sum('kuota');
                $realisasi = $plans->sum(fn ($p) => $p->realisasi);
                return (object) [
                    'company' => $plans->first()->company,
                    'sisa' => max($kuota - $realisasi, 0),
                    'realisasi' => $realisasi,
                    'persentase' => $kuota > 0 ? min(round(($realisasi / $kuota) * 100), 100) : 0,
                ];
            });

        return view('advertorials.media', compact('advertorials', 'companies', 'mediaList', 'tipe', 'quotaSummary'));
    }

    public function create()
    {
        $companies = Company::orderBy('nama_perusahaan')->get();
        $mediaList = Media::orderBy('nama_media')->get();
        $plans = $this->getPlansForDropdown();

        return view('advertorials.create', compact('companies', 'mediaList', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['status'] = $this->determineStatus($validated);
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('file_kwitansi')) {
            $validated['file_kwitansi'] = $request->file('file_kwitansi')->store('kwitansi', 'public');
        }

        Advertorial::create($validated);

        return redirect()->route('advertorials.index')->with('success', 'Data advertorial berhasil ditambahkan.');
    }

    public function edit(Advertorial $advertorial)
    {
        $companies = Company::orderBy('nama_perusahaan')->get();
        $mediaList = Media::orderBy('nama_media')->get();
        $plans = $this->getPlansForDropdown();

        return view('advertorials.edit', compact('advertorial', 'companies', 'mediaList', 'plans'));
    }

    public function update(Request $request, Advertorial $advertorial)
    {
        $validated = $this->validateRequest($request);
        $validated['status'] = $this->determineStatus($validated);

        if ($request->hasFile('file_kwitansi')) {
            $validated['file_kwitansi'] = $request->file('file_kwitansi')->store('kwitansi', 'public');
        } else {
            unset($validated['file_kwitansi']);
        }

        $advertorial->update($validated);

        return redirect()->route('advertorials.index')->with('success', 'Data advertorial berhasil diperbarui.');
    }

    public function destroy(Advertorial $advertorial)
    {
        $advertorial->delete();

        return redirect()->route('advertorials.index')->with('success', 'Data advertorial berhasil dihapus.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'company_id' => 'required|exists:companies,id',
            'media_id' => 'required|exists:media,id',
            'plan_id' => 'nullable|exists:advertising_plans,id',
            'judul' => 'required|string|max:255',
            'waktu_tayang' => 'required|date',
            'tanggal_kwitansi' => 'required|date',
            'status_bayar' => 'required|in:sudah,belum',
            'tanggal_jatuh_tempo' => 'nullable|required_if:status_bayar,belum|date',
            'tanggal_dibayar' => 'nullable|required_if:status_bayar,sudah|date',
            'nominal' => 'required|numeric|min:0',
            'catatan' => 'nullable|string',
            'file_kwitansi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
    }

    private function determineStatus(array &$validated): string
    {
        if ($validated['status_bayar'] === 'sudah') {
            $validated['tanggal_jatuh_tempo'] = null;
            unset($validated['status_bayar']);
            return 'sudah_dibayar';
        }

        $validated['tanggal_dibayar'] = null;
        unset($validated['status_bayar']);

        if (!empty($validated['tanggal_jatuh_tempo']) && \Carbon\Carbon::parse($validated['tanggal_jatuh_tempo'])->isPast()) {
            return 'jatuh_tempo';
        }

        return 'belum_dibayar';
    }

    private function getPlansForDropdown()
    {
        return AdvertisingPlan::orderByDesc('tahun')->orderByDesc('bulan')->get()
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'company_id' => $plan->company_id,
                'media_id' => $plan->media_id,
                'label' => $plan->label_periode . ' (Kuota: ' . $plan->kuota . ', Terisi: ' . $plan->realisasi . ')',
            ]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('media_id')) {
            $query->where('media_id', $request->media_id);
        }
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal_kwitansi', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal_kwitansi', '<=', $request->sampai_tanggal);
        }
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }
    }
}