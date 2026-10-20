<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi tabel competition_scores untuk rekapitulasi penilaian dan klasemen
     */
    public function up(): void
    {
        Schema::create('competition_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignUuid('patrol_id')->constrained('patrols')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->foreignUuid('judge_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('competition_schedule_id')->nullable()->constrained('competition_schedules')->nullOnDelete();
            $table->decimal('final_score', 8, 2)->default(0.00);
            $table->json('criteria_scores')->nullable();
            $table->string('predicate')->nullable(); // prestasi_tinggi, prestasi_baik, prestasi_cukup, partisipasi
            $table->integer('rank')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['competition_id', 'patrol_id'], 'comp_patrol_unique');
            $table->index(['competition_id', 'rank']);
            $table->index(['patrol_id']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_scores');
    }
};
