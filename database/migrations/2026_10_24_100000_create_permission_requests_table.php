<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permohonan izin (meninggalkan perkemahan permanen/sementara dan kunjungan tamu) beserta peserta/bindamping terkait
     */
    public function up(): void
    {
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('letter_number', 60)->nullable()->unique(); // nomor surat izin, dibuat otomatis
            $table->string('type', 30);
            $table->text('reason');
            $table->string('guest_name')->nullable();
            $table->foreignUuid('scout_group_id')->nullable()->constrained('scout_groups')->nullOnDelete();
            $table->string('status', 20)->default('active'); // active = peserta masih di luar, completed = selesai
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('permission_request_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('permission_request_id')->constrained('permission_requests')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->nullable()->constrained('participants')->cascadeOnDelete();
            $table->foreignUuid('chaperone_id')->nullable()->constrained('chaperones')->cascadeOnDelete();
            $table->timestamp('returned_at')->nullable(); // konfirmasi kembali (hanya peserta pada izin sementara)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_request_members');
        Schema::dropIfExists('permission_requests');
    }
};
