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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_questionnaire_open')->default(true)->after('is_certificate_published');
            $table->timestamp('questionnaire_start_at')->nullable()->after('is_questionnaire_open');
            $table->timestamp('questionnaire_end_at')->nullable()->after('questionnaire_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'is_questionnaire_open',
                'questionnaire_start_at',
                'questionnaire_end_at',
            ]);
        });
    }
};
