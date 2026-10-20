<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('religion')->nullable()->after('gender');
            $table->text('home_address')->nullable()->after('blood_type');
            $table->string('city')->default('Padangsidimpuan')->after('home_address');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->string('home_phone', 30)->nullable()->after('postal_code');
            $table->string('phone', 30)->nullable()->after('home_phone'); // Handphone peserta

            // Data Orang Tua
            $table->string('parent_name')->nullable()->after('phone');
            $table->string('parent_job')->nullable()->after('parent_name');
            $table->text('parent_address')->nullable()->after('parent_job');
            $table->string('parent_city')->default('Padangsidimpuan')->after('parent_address');
            $table->string('parent_postal_code', 10)->nullable()->after('parent_city');
            $table->string('parent_home_phone', 30)->nullable()->after('parent_postal_code');
            $table->string('parent_phone', 30)->nullable()->after('parent_home_phone');

            // Riwayat Pendidikan, Siaga, dan Penggalang (JSON)
            $table->json('formal_education')->nullable()->after('parent_phone');
            $table->json('scout_siaga')->nullable()->after('formal_education');
            $table->json('scout_penggalang')->nullable()->after('scout_siaga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'religion',
                'home_address',
                'city',
                'postal_code',
                'home_phone',
                'phone',
                'parent_name',
                'parent_job',
                'parent_address',
                'parent_city',
                'parent_postal_code',
                'parent_home_phone',
                'parent_phone',
                'formal_education',
                'scout_siaga',
                'scout_penggalang',
            ]);
        });
    }
};
