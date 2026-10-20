<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi tabel audit_logs untuk jejak audit penilaian dan perubahan sistem
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // score_draft_saved, score_finalized, score_unlocked, score_corrected
            $table->string('model_type');
            $table->string('model_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'action']);
            $table->index('created_at');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
