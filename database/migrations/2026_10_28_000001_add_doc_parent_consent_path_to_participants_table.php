<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom berkas scan surat izin orang tua / wali peserta
     */
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('doc_parent_consent_path')->nullable()->after('doc_health_certificate_path');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('doc_parent_consent_path');
        });
    }
};
