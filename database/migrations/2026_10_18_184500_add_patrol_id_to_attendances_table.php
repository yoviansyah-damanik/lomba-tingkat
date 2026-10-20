<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom patrol_id pada tabel attendances
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignUuid('patrol_id')->nullable()->after('participant_id')->constrained('patrols')->nullOnDelete();
            $table->index(['attendance_session_id', 'patrol_id']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['patrol_id']);
            $table->dropIndex(['attendance_session_id', 'patrol_id']);
            $table->dropColumn('patrol_id');
        });
    }
};
