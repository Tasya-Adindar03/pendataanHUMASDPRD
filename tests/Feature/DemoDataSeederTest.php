<?php

namespace Tests\Feature;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_covers_six_months_and_can_be_seeded_again(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 31));
        $existing = User::factory()->create();
        $this->seed(DemoDataSeeder::class);
        $count = Advertorial::count();
        $this->seed(DemoDataSeeder::class);

        $this->assertSame(7, User::count());
        $this->assertNotNull($existing->fresh());
        $this->assertSame(24, Company::count());
        $this->assertSame(12, Media::count());
        $this->assertSame(144, AdvertisingPlan::count());
        $this->assertSame($count, Advertorial::count());
        $this->assertGreaterThan(400, $count);
        foreach (['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'] as $status) {
            $this->assertGreaterThan(0, Advertorial::where('status', $status)->count());
        }
        foreach (range(0, 5) as $offset) {
            $month = now()->startOfMonth()->subMonths($offset);
            $this->assertGreaterThan(0, Advertorial::whereYear('tanggal_kwitansi', $month->year)
                ->whereMonth('tanggal_kwitansi', $month->month)->sum('nominal'));
        }
        $this->assertSame(24, AdvertisingPlan::where('bulan', 10)->where('tahun', 2026)->count());
        $this->assertSame(0, Advertorial::whereNull('plan_id')->orWhereNull('created_by')->count());
        $this->assertSame(0, Advertorial::where('tanggal_kwitansi', '>', today())->count());
        $this->get('/dashboard')->assertOk();
        $this->travelBack();
    }
}
