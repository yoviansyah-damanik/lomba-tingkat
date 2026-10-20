<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan konfigurasi privasi identitas regu & pangkalan
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('hide_patrol_identities')->default(false)->after('is_active');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('hide_patrol_identities');
        });
    }
};
