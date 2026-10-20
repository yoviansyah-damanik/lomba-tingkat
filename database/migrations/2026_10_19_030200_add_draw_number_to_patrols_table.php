<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan nomor undian blind scoring pada tabel patrols
     */
    public function up(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->unsignedInteger('draw_number')->nullable()->after('gender_category');
            $table->index(['gender_category', 'draw_number']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->dropIndex(['gender_category', 'draw_number']);
            $table->dropColumn('draw_number');
        });
    }
};
