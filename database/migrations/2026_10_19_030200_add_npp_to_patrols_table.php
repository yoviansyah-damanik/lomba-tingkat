<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan Nomor Pendaftaran Peserta (NPP) pada tabel patrols.
     * NPP dibuat panitia setelah pendaftaran ditutup (2 digit: 01, 02, dst.; putra dan putri dinomori
     * terpisah) dan menjadi satu-satunya identitas regu yang dilihat juri saat penilaian.
     */
    public function up(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->string('npp', 3)->nullable()->after('gender_category');
            $table->unique(['gender_category', 'npp']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->dropUnique(['gender_category', 'npp']);
            $table->dropColumn('npp');
        });
    }
};
