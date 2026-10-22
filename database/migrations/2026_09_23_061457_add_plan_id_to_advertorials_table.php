<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertorials', function (Blueprint $table) {
            $table->foreignId('plan_id')
                ->nullable()
                ->after('media_id')
                ->constrained('advertising_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('advertorials', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};