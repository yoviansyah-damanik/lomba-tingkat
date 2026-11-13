<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('committee_chair_signature_path')->nullable()->after('committee_chair_nta');
            $table->string('kwarcab_chair_signature_path')->nullable()->after('kwarcab_chair_nta');
            $table->boolean('show_signatures_on_certificate')->default(true)->after('is_certificate_published');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'committee_chair_signature_path',
                'kwarcab_chair_signature_path',
                'show_signatures_on_certificate',
            ]);
        });
    }
};

