<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kode undangan registrasi: satu kode untuk satu gugus depan yang diizinkan mendaftar
     */
    public function up(): void
    {
        Schema::create('registration_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 16)->unique();
            $table->string('group_number');
            $table->string('school_name');
            $table->string('kwarran_name', 100);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('used_at')->nullable();
            $table->foreignUuid('scout_group_id')->nullable()->constrained('scout_groups')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_invitations');
    }
};
