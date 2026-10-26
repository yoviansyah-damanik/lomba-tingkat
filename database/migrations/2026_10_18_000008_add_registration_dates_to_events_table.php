<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan konfigurasi batas waktu registrasi pangkalan ke tabel events
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('registration_start_at')->nullable()->after('end_date');
            $table->dateTime('registration_end_at')->nullable()->after('registration_start_at');
            $table->boolean('is_registration_open')->default(true)->after('registration_end_at');

            // Gerbang pendaftaran mandiri aparatur (panitia & juri): tertutup sampai dibuka Superadmin
            $table->boolean('is_official_registration_open')->default(false)->after('is_registration_open');
            $table->dateTime('official_registration_start_at')->nullable()->after('is_official_registration_open');
            $table->dateTime('official_registration_end_at')->nullable()->after('official_registration_start_at');
            $table->string('official_registration_token', 64)->nullable()->after('official_registration_end_at');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'registration_start_at', 'registration_end_at', 'is_registration_open',
                'is_official_registration_open', 'official_registration_start_at', 'official_registration_end_at',
                'official_registration_token',
            ]);
        });
    }
};
