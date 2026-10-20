<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom participant_type pada tabel competition_schedules
     */
    public function up(): void
    {
        Schema::table('competition_schedules', function (Blueprint $table) {
            $table->string('participant_type')->nullable()->after('session_name');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('competition_schedules', function (Blueprint $table) {
            $table->dropColumn('participant_type');
        });
    }
};
