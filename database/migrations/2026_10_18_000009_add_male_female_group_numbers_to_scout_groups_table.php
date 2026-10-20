<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom nomor gudep putra (ganjil) dan putri (genap) ke tabel scout_groups
     */
    public function up(): void
    {
        Schema::table('scout_groups', function (Blueprint $table) {
            $table->string('male_group_number')->nullable()->after('group_number');
            $table->string('female_group_number')->nullable()->after('male_group_number');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('scout_groups', function (Blueprint $table) {
            $table->dropColumn(['male_group_number', 'female_group_number']);
        });
    }
};
