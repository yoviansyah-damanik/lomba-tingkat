<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom konfigurasi biaya pendaftaran dan rekening pembayaran ke tabel events
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('registration_fee_per_patrol')->nullable()->after('is_registration_open');
            $table->string('payment_bank_name')->nullable()->after('registration_fee_per_patrol');
            $table->string('payment_account_number')->nullable()->after('payment_bank_name');
            $table->string('payment_account_holder')->nullable()->after('payment_account_number');
            $table->text('payment_instructions')->nullable()->after('payment_account_holder');
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'registration_fee_per_patrol',
                'payment_bank_name',
                'payment_account_number',
                'payment_account_holder',
                'payment_instructions',
            ]);
        });
    }
};
