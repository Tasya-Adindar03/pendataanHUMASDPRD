<?php

namespace Tests\Feature;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_dashboard_has_nine_charts_and_zero_totals(): void
    {
        $this->get('/dashboard')->assertOk()
            ->assertViewHas('totalData', 0)
            ->assertViewHas('paidRate', 0)
            ->assertViewHas('growth', null)
            ->assertViewHas('charts', fn ($charts) => count($charts['monthly']) === 6 && $charts['planCounts'] === [0, 0, 0])
            ->assertSee('id="chart-', false);
    }

    public function test_dashboard_aggregates_period_payment_dates_aging_and_capped_quota(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 31)->startOfDay());
        $company = Company::create(['nama_perusahaan' => 'Contoh']);
        $media = Media::create(['nama_media' => 'Cetak', 'tipe' => 'cetak']);
        $plan = AdvertisingPlan::create([
            'company_id' => $company->id, 'media_id' => $media->id,
            'bulan' => 10, 'tahun' => 2026, 'kuota' => 1,
        ]);
        $create = function ($invoice, $date, $nominal, $status, $extra = []) use ($company, $media) {
            return Advertorial::create($extra + [
                'company_id' => $company->id, 'media_id' => $media->id,
                'judul' => 'Publikasi contoh', 'no_invoice' => $invoice,
                'waktu_tayang' => $date, 'tanggal_kwitansi' => $date,
                'nominal' => $nominal, 'status' => $status,
            ]);
        };
        $create('A', '2026-05-10', 1000000, 'sudah_dibayar', ['tanggal_dibayar' => '2026-06-02']);
        $create('B', '2026-10-10', 2000000, 'belum_dibayar', ['tanggal_jatuh_tempo' => '2026-10-01', 'plan_id' => $plan->id]);
        $create('C', '2026-10-10', 3000000, 'jatuh_tempo', ['tanggal_jatuh_tempo' => '2026-07-01', 'plan_id' => $plan->id]);
        $create('D', '2026-10-10', 4000000, 'belum_dibayar');
        $create('OLD', '2026-04-01', 5000000, 'sudah_dibayar', ['tanggal_dibayar' => '2026-10-01']);
        $create('FUTURE', '2026-11-01', 6000000, 'sudah_dibayar', ['tanggal_dibayar' => '2026-11-01']);
        $create('DELETED', '2026-10-01', 9000000, 'belum_dibayar')->delete();

        $this->get('/dashboard')->assertOk()
            ->assertViewHas('totalData', 4)
            ->assertViewHas('totalNominal', 10000000)
            ->assertViewHas('paidRate', 10)
            ->assertViewHas('overdueTotal', 5000000)
            ->assertViewHas('realized', 1)
            ->assertViewHas('planRate', 100)
            ->assertViewHas('charts', function ($charts) {
                $this->assertSame('2026-05-01', $charts['monthly'][0]['from']);
                $this->assertSame('2026-10-31', $charts['monthly'][5]['to']);
                $this->assertEquals(1000000, $charts['monthly'][1]['paid']);
                $this->assertEquals(5000000, $charts['monthly'][5]['paid']);
                $this->assertEquals([0, 2000000, 0, 3000000, 4000000], $charts['aging']);
                $this->assertSame([1, 2, 1], $charts['statusCounts']);
                $this->assertSame([1, 0, 0], $charts['planCounts']);
                return true;
            });
        $this->travelBack();
    }
}
