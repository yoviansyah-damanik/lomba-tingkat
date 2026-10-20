<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom nomor identitas tergeneralisasi (ID-XXXXXXXX)
     * untuk setiap peserta, pembina pendamping, dan panitia/juri
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_number', 20)->nullable()->unique()->after('id');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->string('id_number', 20)->nullable()->unique()->after('id');
        });

        Schema::table('chaperones', function (Blueprint $table) {
            $table->string('id_number', 20)->nullable()->unique()->after('id');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('chaperones', function (Blueprint $table) {
            $table->dropColumn('id_number');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('id_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_number');
        });
    }
};
