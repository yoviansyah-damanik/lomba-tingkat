<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk menambahkan qr_token dan atribut staf panitia/juri pada tabel users
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('remember_token');
            $table->dateTime('qr_generated_at')->nullable()->after('qr_token');
            $table->string('scout_id', 50)->nullable()->after('phone_number'); // NTA Pramuka Staf
            $table->string('institution', 150)->nullable()->after('scout_id'); // Asal Pangkalan/Instansi
            $table->string('position', 100)->nullable()->after('institution'); // Jabatan/Bidang Tugas
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'qr_generated_at', 'scout_id', 'institution', 'position']);
        });
    }
};
