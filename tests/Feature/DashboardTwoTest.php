<?php

namespace Tests\Feature;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_month_controls_metrics_calendar_and_plans(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 23)->startOfDay());
        $company = Company::create(['nama_perusahaan' => 'Perusahaan A']);
        $media = Media::create(['nama_media' => 'Media A', 'tipe' => 'online']);
        $plan = AdvertisingPlan::create(['company_id' => $company->id, 'media_id' => $media->id, 'bulan' => 9, 'tahun' => 2026, 'kuota' => 1]);
        $base = ['company_id' => $company->id, 'media_id' => $media->id, 'judul' => 'Publikasi'];
        Advertorial::create($base + ['no_invoice' => 'SEP-1', 'tanggal_kwitansi' => '2026-09-02', 'waktu_tayang' => '2026-09-02', 'nominal' => 1000000, 'status' => 'sudah_dibayar', 'tanggal_dibayar' => '2026-10-01', 'plan_id' => $plan->id]);
        Advertorial::create($base + ['no_invoice' => 'SEP-2', 'tanggal_kwitansi' => '2026-09-10', 'waktu_tayang' => '2026-09-10', 'nominal' => 3000000, 'status' => 'belum_dibayar', 'tanggal_jatuh_tempo' => '2026-09-30']);
        Advertorial::create($base + ['no_invoice' => 'AUG', 'tanggal_kwitansi' => '2026-08-01', 'waktu_tayang' => '2026-08-01', 'nominal' => 2000000, 'status' => 'belum_dibayar']);
        Advertorial::create($base + ['no_invoice' => 'OCT', 'tanggal_kwitansi' => '2026-10-01', 'waktu_tayang' => '2026-10-01', 'nominal' => 9000000, 'status' => 'belum_dibayar']);

        $this->get('/dashboard-2?bulan=2026-09')->assertOk()
            ->assertViewHas('total', 4000000)->assertViewHas('paid', 1000000)
            ->assertViewHas('overdue', 3000000)->assertViewHas('growth', 100)
            ->assertViewHas('paidRate', 25)->assertViewHas('realized', 1)
            ->assertViewHas('chartData', function ($data) {
                $this->assertCount(30, $data['daily']);
                $this->assertEquals(4000000, $data['daily'][29]['cumulative']);
                $this->assertEquals(1000000, $data['daily'][29]['paid']);
                $this->assertEquals([0, 3000000, 0, 0, 0], $data['aging']);
                $this->assertEquals([0, 4000000], $data['type']);
                return true;
            })->assertSee('September 2026')->assertSee('value="2026-09"', false);
        $this->get('/dashboard-2?bulan=2026-09&tipe=cetak')->assertOk()
            ->assertViewHas('total', 0)->assertViewHas('quota', 0);
        $this->get('/dashboard-2?bulan=2026-09&status=belum_dibayar&hari=10&aging=1')->assertOk()
            ->assertViewHas('total', 3000000)->assertViewHas('count', 1)
            ->assertViewHas('growth', null)->assertViewHas('quota', 1)
            ->assertViewHas('detailRows', fn ($rows) => $rows->first()->no_invoice === 'SEP-2');
        $this->get('/dashboard-2?bulan=2026-09&company_id=999999')->assertOk()->assertViewHas('total', 0);
        $this->get('/dashboard-2?bulan=2026-09&aging=0')->assertOk()->assertViewHas('count', 0);
        $this->getJson('/dashboard-2?status=invalid&aging=9')->assertUnprocessable()->assertJsonValidationErrors(['status', 'aging']);
        $this->travelBack();
    }

    public function test_empty_leap_month_and_invalid_month_are_handled(): void
    {
        $this->get('/dashboard-2?bulan=2024-02')->assertOk()
            ->assertViewHas('total', 0)->assertViewHas('paidRate', 0)
            ->assertViewHas('growth', null)
            ->assertViewHas('daily', fn ($days) => count($days) === 29);
        $this->getJson('/dashboard-2?bulan=2026-13')->assertUnprocessable()->assertJsonValidationErrors('bulan');
        $this->getJson('/dashboard-2?bulan=not-a-month')->assertUnprocessable()->assertJsonValidationErrors('bulan');
        $this->get('/dashboard-2')->assertOk();
    }
}
