<?php

namespace App\Console\Commands;

use App\Models\Advertorial;
use Illuminate\Console\Command;

class UpdateOverdueStatus extends Command
{
    protected $signature = 'advertorial:update-overdue';

    protected $description = 'Ubah status belum_dibayar menjadi jatuh_tempo jika tenggat sudah lewat';

    public function handle(): int
    {
        $jumlah = Advertorial::syncOverdue();

        $this->info("{$jumlah} data diperbarui menjadi Jatuh Tempo.");

        return self::SUCCESS;
    }
}