<?php

namespace App\Http\Controllers;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const BULAN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function index(Request $request)
    {
        [$periode, $periodeLabel, $from, $to] = $this->resolvePeriod($request);

        // Query dasar: seluruh data pada periode terpilih (berdasarkan tanggal kwitansi)
        $base = fn () => Advertorial::query()->when(
            $from,
            fn ($q) => $q->whereBetween('tanggal_kwitansi', [$from->toDateString(), $to->toDateString()])
        );

        // ===== Kartu angka =====
        $totalData = $base()->count();
        $totalNominal = (float) $base()->sum('nominal');
        $totalSudahDibayar = (float) $base()->where('status', 'sudah_dibayar')->sum('nominal');
        $totalBelumDibayar = (float) $base()->whereIn('status', ['belum_dibayar', 'jatuh_tempo'])->sum('nominal');
        $jumlahJatuhTempo = $base()->where('status', 'jatuh_tempo')->count();

        // ===== Perbandingan dengan periode sebelumnya (panjangnya sama) =====
        $delta = null;
        $prevText = null;

        if ($from) {
            $days = (int) abs($from->diffInDays($to)) + 1;
            $prevTo = $from->copy()->subDay();
            $prevFrom = $prevTo->copy()->subDays($days - 1);

            $prev = fn () => Advertorial::query()
                ->whereBetween('tanggal_kwitansi', [$prevFrom->toDateString(), $prevTo->toDateString()]);

            $delta = [
                'data' => $this->persen($totalData, $prev()->count()),
                'nominal' => $this->persen($totalNominal, (float) $prev()->sum('nominal')),
                'sudah' => $this->persen($totalSudahDibayar, (float) $prev()->where('status', 'sudah_dibayar')->sum('nominal')),
                'belum' => $this->persen($totalBelumDibayar, (float) $prev()->whereIn('status', ['belum_dibayar', 'jatuh_tempo'])->sum('nominal')),
            ];

            $prevText = $this->tgl($prevFrom) . ' – ' . $this->tgl($prevTo);
        }

        // ===== Grafik status & tipe =====
        $statusData = [
            'sudah_dibayar' => $base()->where('status', 'sudah_dibayar')->count(),
            'belum_dibayar' => $base()->where('status', 'belum_dibayar')->count(),
            'jatuh_tempo' => $base()->where('status', 'jatuh_tempo')->count(),
        ];

        $tipeData = [
            'cetak' => (float) $base()->cetak()->sum('nominal'),
            'online' => (float) $base()->online()->sum('nominal'),
        ];

        // ===== Top 5 perusahaan =====
        $topCompanies = $base()
            ->selectRaw('company_id, SUM(nominal) as total')
            ->with('company')
            ->groupBy('company_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ===== Grafik tren (harian bila <= 31 hari, selain itu bulanan) =====
        $trendFrom = $from;
        $trendTo = $to;

        if (!$from) {
            $min = Advertorial::min('tanggal_kwitansi');
            $max = Advertorial::max('tanggal_kwitansi');

            $trendFrom = $min ? Carbon::parse($min)->startOfDay() : today()->subMonthsNoOverflow(5)->startOfMonth();
            $trendTo = today();

            if ($max && Carbon::parse($max)->gt($trendTo)) {
                $trendTo = Carbon::parse($max)->startOfDay();
            }
        }

        $span = (int) abs($trendFrom->diffInDays($trendTo)) + 1;
        $trendLabels = [];
        $trendValues = [];
        $trendRanges = [];

        if ($span <= 31) {
            $trendGranularity = 'harian';

            $totals = $base()
                ->selectRaw('DATE(tanggal_kwitansi) as d, SUM(nominal) as total')
                ->groupBy('d')
                ->pluck('total', 'd');

            for ($d = $trendFrom->copy(); $d->lte($trendTo); $d->addDay()) {
                $key = $d->toDateString();
                $trendLabels[] = $d->day . ' ' . self::BULAN[$d->month];
                $trendValues[] = (float) ($totals[$key] ?? 0);
                $trendRanges[] = ['dari' => $key, 'sampai' => $key];
            }
        } else {
            $trendGranularity = 'bulanan';

            $start = $trendFrom->copy()->startOfMonth();
            $end = $trendTo->copy()->startOfMonth();

            if ($start->diffInMonths($end) > 35) {
                $start = $end->copy()->subMonthsNoOverflow(35);
            }

            $totals = $base()
                ->selectRaw("DATE_FORMAT(tanggal_kwitansi, '%Y-%m') as ym, SUM(nominal) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');

            for ($m = $start->copy(); $m->lte($end); $m->addMonthNoOverflow()) {
                $dari = $m->copy()->startOfMonth();
                $sampai = $m->copy()->endOfMonth()->startOfDay();

                if ($from) {
                    if ($dari->lt($from)) {
                        $dari = $from->copy();
                    }
                    if ($sampai->gt($to)) {
                        $sampai = $to->copy();
                    }
                }

                $trendLabels[] = self::BULAN[$m->month] . ' ' . $m->year;
                $trendValues[] = (float) ($totals[$m->format('Y-m')] ?? 0);
                $trendRanges[] = ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()];
            }
        }

        // ===== Kondisi saat ini (tidak mengikuti periode) =====
        $planBulanIni = AdvertisingPlan::with(['company', 'media'])
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->get();

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

        // ===== Bahan untuk tampilan pemilih periode =====
        $rangeParams = $from
            ? ['dari_tanggal' => $from->toDateString(), 'sampai_tanggal' => $to->toDateString()]
            : [];

        $rangeText = !$from
            ? 'Seluruh data'
            : ($from->isSameDay($to) ? $this->tgl($from) : $this->tgl($from) . ' – ' . $this->tgl($to));

        $presets = [
            'today' => 'Hari ini',
            'yesterday' => 'Kemarin',
            'last7' => '7 hari terakhir',
            'last30' => '30 hari terakhir',
            'this_month' => 'Bulan ini',
            'last_month' => 'Bulan lalu',
            'last3m' => '3 bulan terakhir',
            'this_year' => 'Tahun ini',
            'last_year' => 'Tahun lalu',
            'all' => 'Semua waktu',
        ];

        $dariInput = $from?->toDateString();
        $sampaiInput = $to?->toDateString();

        return view('dashboard.index', compact(
            'totalData',
            'totalNominal',
            'totalSudahDibayar',
            'totalBelumDibayar',
            'jumlahJatuhTempo',
            'delta',
            'prevText',
            'statusData',
            'tipeData',
            'topCompanies',
            'trendLabels',
            'trendValues',
            'trendRanges',
            'trendGranularity',
            'planBulanIni',
            'jumlahTerlambat',
            'nominalTerlambat',
            'daftarTerlambat',
            'jumlahSegera',
            'nominalSegera',
            'daftarSegera',
            'periode',
            'periodeLabel',
            'rangeText',
            'rangeParams',
            'presets',
            'dariInput',
            'sampaiInput'
        ));
    }

    // Menentukan rentang tanggal dari pilihan periode
    private function resolvePeriod(Request $request): array
    {
        $today = today();
        $periode = (string) $request->get('periode', 'this_month');

        if ($periode === 'custom') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', (string) $request->get('dari'))->startOfDay();
                $to = Carbon::createFromFormat('Y-m-d', (string) $request->get('sampai'))->startOfDay();

                if ($from->gt($to)) {
                    [$from, $to] = [$to, $from];
                }

                return ['custom', 'Rentang kustom', $from, $to];
            } catch (\Throwable $e) {
                $periode = 'this_month';
            }
        }

        switch ($periode) {
            case 'today':
                return ['today', 'Hari ini', $today->copy(), $today->copy()];

            case 'yesterday':
                $d = $today->copy()->subDay();
                return ['yesterday', 'Kemarin', $d, $d->copy()];

            case 'last7':
                return ['last7', '7 hari terakhir', $today->copy()->subDays(6), $today->copy()];

            case 'last30':
                return ['last30', '30 hari terakhir', $today->copy()->subDays(29), $today->copy()];

            case 'last_month':
                $first = $today->copy()->subMonthNoOverflow()->startOfMonth();
                return ['last_month', 'Bulan lalu', $first, $first->copy()->endOfMonth()->startOfDay()];

            case 'last3m':
                return ['last3m', '3 bulan terakhir', $today->copy()->subMonthsNoOverflow(2)->startOfMonth(), $today->copy()];

            case 'this_year':
                return ['this_year', 'Tahun ini', $today->copy()->startOfYear(), $today->copy()];

            case 'last_year':
                $y = $today->copy()->subYear();
                return ['last_year', 'Tahun lalu', $y->copy()->startOfYear(), $y->copy()->endOfYear()->startOfDay()];

            case 'all':
                return ['all', 'Semua waktu', null, null];

            default:
                return ['this_month', 'Bulan ini', $today->copy()->startOfMonth(), $today->copy()];
        }
    }

    // Selisih dalam persen; 'baru' bila periode sebelumnya kosong
    private function persen($now, $before)
    {
        if ((float) $before === 0.0) {
            return (float) $now === 0.0 ? 0.0 : 'baru';
        }

        return round((($now - $before) / $before) * 100, 1);
    }

    private function tgl(Carbon $d): string
    {
        return $d->day . ' ' . self::BULAN[$d->month] . ' ' . $d->year;
    }
}