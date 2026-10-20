<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi tabel competitions dan competition_schedules
     */
    public function up(): void
    {
        // Tabel master cabang lomba (competitions)
        Schema::create('competitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->nullable()->constrained('events')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('category')->default('all'); // boys, girls, all
            $table->string('participant_type')->default('patrol'); // patrol, individual
            $table->text('description')->nullable();
            $table->string('guidelines_pdf_path')->nullable();
            $table->text('guidelines_text')->nullable();
            $table->string('scoring_formula')->default('average'); // average, sum
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Tabel jadwal pelaksanaan lomba (competition_schedules)
        Schema::create('competition_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('session_name');
            $table->string('arena_location');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('checkin_open_time')->nullable();
            $table->dateTime('checkin_close_time')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, rescheduled, cancelled
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['competition_id', 'start_time', 'is_published'], 'comp_sched_time_pub_idx');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_schedules');
        Schema::dropIfExists('competitions');
    }
};
