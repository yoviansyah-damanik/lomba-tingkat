<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi tabel assessments untuk penilaian individual oleh juri
     */
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignUuid('competition_schedule_id')->constrained('competition_schedules')->cascadeOnDelete();
            $table->foreignUuid('patrol_id')->constrained('patrols')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->foreignUuid('judge_id')->constrained('users')->cascadeOnDelete();
            $table->json('criteria_scores')->nullable();
            $table->decimal('raw_total_score', 8, 2)->default(0.00);
            $table->decimal('final_score', 8, 2)->default(0.00);
            $table->string('status')->default('draft'); // draft, finalized
            $table->text('notes')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->dateTime('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['competition_schedule_id', 'judge_id', 'patrol_id'], 'sched_judge_patrol_unique');
            $table->index(['competition_id', 'patrol_id']);
            $table->index(['judge_id', 'status']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
