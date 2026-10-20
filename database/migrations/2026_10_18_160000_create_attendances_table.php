<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk tabel presensi multi-entitas
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            
            // Tipe entitas yang hadir: participant, chaperone, user (panitia/juri)
            $table->string('entity_type')->default('participant'); // participant, chaperone, user
            $table->foreignUuid('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->foreignUuid('chaperone_id')->nullable()->constrained('chaperones')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Petugas scanner & waktu server
            $table->foreignUuid('scanned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scanned_at');
            $table->string('status')->default('present'); // present, late, excused, absent
            $table->string('notes')->nullable();
            $table->string('device_info')->nullable();
            $table->timestamps();

            // Indeks dan constraint untuk mencegah presensi ganda dalam satu sesi
            $table->index(['attendance_session_id', 'status']);
            $table->index(['attendance_session_id', 'scanned_at']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
