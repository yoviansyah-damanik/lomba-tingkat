<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk roles dan ekstensi tabel users
     */
    public function up(): void
    {
        // Menambahkan kolom pelengkap pada tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('username');
            $table->string('avatar_path')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('avatar_path');
            $table->uuid('participant_id')->nullable()->index()->after('is_active'); // akun peserta -> data peserta
        });

        // Membuat tabel master roles
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Membuat tabel pivot relasi pengguna dengan peran (many-to-many untuk mendukung rangkap panitia & juri)
        Schema::create('role_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'avatar_path', 'is_active', 'participant_id']);
        });
    }
};
