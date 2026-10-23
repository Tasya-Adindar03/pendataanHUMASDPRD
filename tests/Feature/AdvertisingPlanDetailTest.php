<?php

namespace Tests\Feature;

use App\Models\AdvertisingPlan;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingPlanDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_detail_shows_only_linked_advertorials_and_handles_empty_and_missing_plans(): void
    {
        $this->seed(DemoDataSeeder::class);
        $plan = AdvertisingPlan::firstOrFail();
        $linked = $plan->advertorials()->firstOrFail();
        $unrelated = AdvertisingPlan::where('id', '!=', $plan->id)->firstOrFail()->advertorials()->firstOrFail();
        $this->get(route('plans.show', $plan))->assertOk()
            ->assertViewHas('plan', fn ($row) => $row->id === $plan->id)
            ->assertSee($linked->no_invoice)
            ->assertDontSee($unrelated->no_invoice)
            ->assertViewHas('totalNominal', $plan->advertorials()->sum('nominal'));

        $empty = AdvertisingPlan::create([
            'company_id' => $plan->company_id, 'media_id' => $plan->media_id,
            'bulan' => 1, 'tahun' => 2030, 'kuota' => 10,
        ]);
        $this->get(route('plans.show', $empty))->assertOk()
            ->assertSee('Belum ada advertorial yang terhubung');
        $empty->delete();
        $this->get(route('plans.show', $empty))->assertNotFound();
        $this->get('/plans/999999')->assertNotFound();
    }
}
