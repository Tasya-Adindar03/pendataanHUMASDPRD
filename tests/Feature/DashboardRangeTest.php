<?php

namespace Tests\Feature;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_dashboards_support_inclusive_ranges_across_years(): void
    {
        $company = Company::create(['nama_perusahaan' => 'Mitra']);
        $media = Media::create(['nama_media' => 'Media', 'tipe' => 'cetak']);
        foreach (['2025-11-30' => 900, '2025-12-01' => 100, '2026-01-31' => 200, '2026-02-01' => 900] as $date => $value) {
            Advertorial::create(['company_id' => $company->id, 'media_id' => $media->id,
                'no_invoice' => $date, 'judul' => 'Contoh', 'tanggal_kwitansi' => $date,
                'waktu_tayang' => $date, 'nominal' => $value, 'status' => 'belum_dibayar']);
        }
        foreach ([[12, 2025], [1, 2026], [2, 2026]] as [$month, $year]) {
            AdvertisingPlan::create(['company_id' => $company->id, 'media_id' => $media->id, 'bulan' => $month, 'tahun' => $year, 'kuota' => 5]);
        }
        $range = '?bulan_awal=2025-12&bulan_akhir=2026-01';
        $this->get('/dashboard'.$range)->assertOk()->assertViewHas('totalNominal', 300)
            ->assertViewHas('monthCount', 2)->assertViewHas('quota', 10)
            ->assertViewHas('charts', fn ($charts) => count($charts['monthly']) === 2);
        $this->get('/dashboard-2'.$range)->assertOk()->assertViewHas('total', 300)
            ->assertViewHas('quota', 10)->assertViewHas('daily', fn ($days) => count($days) === 62)
            ->assertViewHas('monthTiles', fn ($tiles) => count($tiles) === 2)
            ->assertSee('Perbandingan');
        $this->get('/dashboard-2'.$range.'&tanggal=2026-01-31')->assertOk()->assertViewHas('total', 200);
        $this->get('/dashboard-2'.$range.'&periode=2025-12')->assertOk()->assertViewHas('total', 100);
    }

    public function test_invalid_reversed_or_excessive_ranges_are_rejected(): void
    {
        foreach (['/dashboard', '/dashboard-2'] as $url) {
            foreach (['bulan_awal=invalid', 'bulan_awal=2026-10&bulan_akhir=2026-01', 'bulan_awal=2020-01&bulan_akhir=2026-01'] as $query) {
                $this->getJson($url.'?'.$query)->assertUnprocessable();
            }
        }
    }
}
