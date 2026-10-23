<?php

namespace Tests\Feature;

use App\Models\Advertorial;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertorialEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_renders_selected_plan_and_saves_existing_advertorial(): void
    {
        $this->seed(DemoDataSeeder::class);
        $advertorial = Advertorial::where('status', 'sudah_dibayar')->firstOrFail();
        $this->get(route('advertorials.edit', $advertorial))->assertOk()
            ->assertSee('id="plan_id"', false)
            ->assertSee('value="'.$advertorial->plan_id.'" selected', false)
            ->assertSee($advertorial->judul);

        $this->put(route('advertorials.update', $advertorial), [
            'company_id' => $advertorial->company_id,
            'media_id' => $advertorial->media_id,
            'plan_id' => $advertorial->plan_id,
            'judul' => 'Judul hasil perubahan',
            'waktu_tayang' => $advertorial->waktu_tayang->toDateString(),
            'tanggal_kwitansi' => $advertorial->tanggal_kwitansi->toDateString(),
            'tanggal_dibayar' => $advertorial->tanggal_dibayar->toDateString(),
            'status_bayar' => 'sudah',
            'nominal' => $advertorial->nominal,
        ])->assertSessionHasNoErrors()->assertRedirect(route('advertorials.index'));
        $this->assertDatabaseHas('advertorials', [
            'id' => $advertorial->id,
            'judul' => 'Judul hasil perubahan',
            'plan_id' => $advertorial->plan_id,
            'status' => 'sudah_dibayar',
        ]);

        $advertorial->update(['plan_id' => null]);
        $this->get(route('advertorials.edit', $advertorial))->assertOk();
    }
}
