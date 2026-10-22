<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertorials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('media_id')
                ->constrained('media')
                ->cascadeOnDelete();

            $table->string('judul');
            $table->date('waktu_tayang');
            $table->date('tanggal_kwitansi');
            $table->date('tanggal_dibayar')->nullable();

            $table->decimal('nominal', 15, 2)->default(0);

            $table->enum('status', ['belum_dibayar', 'sudah_dibayar', 'jatuh_tempo'])
                ->default('belum_dibayar');

            $table->string('no_invoice', 50)->unique();
            $table->string('file_kwitansi')->nullable();
            $table->text('catatan')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // index untuk mempercepat filter & chart
            $table->index(['status']);
            $table->index(['tanggal_kwitansi']);
            $table->index(['waktu_tayang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertorials');
    }
};