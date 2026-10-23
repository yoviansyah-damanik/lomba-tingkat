<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom berkas persyaratan: per peserta (7 berkas) dan per pangkalan (Surat Tugas & Bukti Bayar)
     */
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('doc_kta_path')->nullable()->after('photo_path');
            $table->string('doc_bpjs_path')->nullable()->after('doc_kta_path');
            $table->string('doc_health_certificate_path')->nullable()->after('doc_bpjs_path');
            $table->string('doc_birth_certificate_path')->nullable()->after('doc_health_certificate_path');
            $table->string('doc_family_card_path')->nullable()->after('doc_birth_certificate_path');
            $table->string('doc_sku_path')->nullable()->after('doc_family_card_path');
            $table->string('doc_skk_path')->nullable()->after('doc_sku_path');
        });

        Schema::table('scout_groups', function (Blueprint $table) {
            $table->string('mandate_letter_path')->nullable();
            $table->string('payment_proof_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'doc_kta_path', 'doc_bpjs_path', 'doc_health_certificate_path',
                'doc_birth_certificate_path', 'doc_family_card_path', 'doc_sku_path', 'doc_skk_path',
            ]);
        });

        Schema::table('scout_groups', function (Blueprint $table) {
            $table->dropColumn(['mandate_letter_path', 'payment_proof_path']);
        });
    }
};
