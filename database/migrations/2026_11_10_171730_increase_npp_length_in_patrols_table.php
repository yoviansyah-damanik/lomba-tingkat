<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memperbesar panjang kolom npp untuk mendukung format LT-PA-XX dan LT-PI-XX
     */
    public function up(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->string('npp', 20)->nullable()->change();
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->string('npp', 3)->nullable()->change();
        });
    }
};
