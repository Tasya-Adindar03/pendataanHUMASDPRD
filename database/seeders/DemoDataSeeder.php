<?php

namespace Database\Seeders;

use App\Models\Advertorial;
use App\Models\AdvertisingPlan;
use App\Models\Company;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $users = collect(range(1, 6))->map(fn ($i) => User::firstOrCreate(
                ['email' => "petugas{$i}@demo.example"],
                ['name' => "Petugas Demo {$i}", 'password' => Hash::make(Str::random(32))]
            ));

            $companies = collect(range(1, 24))->map(fn ($i) => Company::firstOrCreate(
                ['email' => "perusahaan{$i}@demo.example"],
                [
                    'nama_perusahaan' => sprintf('PT Mitra Publikasi Demo %02d', $i),
                    'pic_nama' => "Penanggung Jawab Demo {$i}",
                    'pic_kontak' => sprintf('08120000%04d', $i),
                    'alamat' => "Jalan Contoh Nomor {$i}, Jakarta (data simulasi)",
                ]
            ));

            $media = collect(range(1, 12))->map(fn ($i) => Media::firstOrCreate(
                ['nama_media' => sprintf('Media Demo %s %02d', $i <= 6 ? 'Cetak' : 'Online', $i)],
                [
                    'tipe' => $i <= 6 ? 'cetak' : 'online',
                    'rate_card' => 1500000 + $i * 250000,
                    'pic_kontak' => sprintf('08130000%04d', $i),
                ]
            ));

            $today = now()->startOfDay();
            for ($offset = 5; $offset >= 0; $offset--) {
                $month = $today->copy()->startOfMonth()->subMonths($offset);
                foreach ($companies as $index => $company) {
                    $outlet = $media[$index % $media->count()];
                    $plan = AdvertisingPlan::firstOrCreate([
                        'company_id' => $company->id,
                        'media_id' => $outlet->id,
                        'bulan' => $month->month,
                        'tahun' => $month->year,
                    ], [
                        'kuota' => 2 + $index % 5,
                        'catatan' => 'Data simulasi enam bulan untuk dashboard.',
                    ]);

                    $count = 1 + ($index + $offset) % 5;
                    for ($item = 1; $item <= $count; $item++) {
                        $receipt = $month->copy()->addDays(($index + $item * 3) % 28);
                        if ($receipt->gt($today)) {
                            $receipt = $today->copy();
                        }
                        $status = ['sudah_dibayar', 'belum_dibayar', 'jatuh_tempo'][($index + $item + $offset) % 3];
                        $due = $status === 'belum_dibayar'
                            ? $today->copy()->addDays(7 + $index % 21)
                            : ($status === 'jatuh_tempo'
                                ? $receipt->copy()->min($today->copy()->subDays(1))
                                : $receipt->copy()->addDays(14));

                        Advertorial::updateOrCreate([
                            'no_invoice' => sprintf('DEMO-%s-C%02d-%02d', $month->format('Ym'), $index + 1, $item),
                        ], [
                            'company_id' => $company->id,
                            'media_id' => $outlet->id,
                            'plan_id' => $plan->id,
                            'judul' => sprintf('Publikasi Demo %s - Perusahaan %02d Edisi %d', $month->format('m/Y'), $index + 1, $item),
                            'waktu_tayang' => $receipt,
                            'tanggal_kwitansi' => $receipt,
                            'tanggal_jatuh_tempo' => $due,
                            'tanggal_dibayar' => $status === 'sudah_dibayar' ? $receipt : null,
                            'nominal' => $outlet->rate_card + $item * 100000 + $offset * 150000,
                            'status' => $status,
                            'created_by' => $users[$index % $users->count()]->id,
                            'catatan' => 'Data simulasi; bukan transaksi nyata.',
                        ]);
                    }
                }
            }
        });
    }
}
