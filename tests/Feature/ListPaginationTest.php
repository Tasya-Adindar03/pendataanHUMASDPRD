<?php

namespace Tests\Feature;

use App\Models\Media;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_use_shared_pagination_and_preserve_filters_on_next_pages(): void
    {
        $this->seed(DemoDataSeeder::class);
        foreach (range(1, 4) as $i) {
            Media::create(['nama_media' => "Media Demo Tambahan {$i}", 'tipe' => 'online']);
        }

        foreach ([
            ['/companies?search=Demo', 'companies', 'search=Demo', 24],
            ['/media?search=Demo', 'mediaList', 'search=Demo', 16],
            ['/advertorials?status=sudah_dibayar', 'advertorials', 'status=sudah_dibayar', null],
            ['/advertorials/media?tipe=online', 'advertorials', 'tipe=online', null],
            ['/plans?bulan='.now()->month, 'planPages', 'bulan='.now()->month, 24],
        ] as [$url, $variable, $filter, $total]) {
            $first = $this->get($url)->assertOk()
                ->assertSee('class="list-pagination"', false)
                ->assertSee('aria-label="Navigasi halaman daftar"', false)
                ->assertSee($filter, false)
                ->assertViewHas($variable, fn ($paginator) => $paginator->count() === 15 && ($total === null || $paginator->total() === $total));
            $firstIds = $first->viewData($variable)->pluck('id')->all();
            $second = $this->get($url.'&page=2')->assertOk()
                ->assertViewHas($variable, fn ($paginator) => $paginator->currentPage() === 2 && $paginator->count() > 0);
            $this->assertEmpty(array_intersect($firstIds, $second->viewData($variable)->pluck('id')->all()));
        }

        $this->get('/companies?search=does-not-exist')->assertOk()
            ->assertViewHas('companies', fn ($paginator) => $paginator->total() === 0)
            ->assertDontSee('aria-label="Navigasi halaman daftar"', false);
    }
}
