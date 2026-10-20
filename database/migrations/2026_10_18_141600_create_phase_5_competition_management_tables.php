<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk kriteria penilaian rubrik, penugasan juri, dan sesi presensi pos
     */
    public function up(): void
    {
        // 1. Tabel Kriteria Penilaian Rubrik Lomba
        Schema::create('assessment_criteria', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->decimal('min_score', 8, 2)->default(0.00);
            $table->decimal('max_score', 8, 2)->default(100.00);
            $table->decimal('weight_percentage', 5, 2)->default(100.00);
            $table->integer('order_index')->default(1);
            $table->timestamps();

            $table->index(['competition_id', 'order_index']);
        });

        // 2. Tabel Penugasan Juri ke Jadwal Sesi Lomba
        Schema::create('judge_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_schedule_id')->constrained('competition_schedules')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('scoring_judge'); // lead_judge, scoring_judge, companion_judge
            $table->boolean('is_confirmed')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['competition_schedule_id', 'user_id'], 'sched_judge_unique');
        });

        // 3. Tabel Sesi Presensi Pos Lomba
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_schedule_id')->constrained('competition_schedules')->cascadeOnDelete();
            $table->string('session_type')->default('participant'); // participant, judge, committee, chaperone
            $table->string('title');
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['competition_schedule_id', 'is_active']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('judge_assignments');
        Schema::dropIfExists('assessment_criteria');
    }
};
