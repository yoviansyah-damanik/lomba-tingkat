<?php

use App\Models\Competition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->boolean('is_mandatory')->default(false)->after('scoring_formula');
            $table->boolean('allows_patrol_inspection')->default(false)->after('is_mandatory');
        });

        // Setel otomatis untuk mata lomba Administrasi Regu bawaan
        Competition::where('code', 'ADM-09')->update([
            'is_mandatory' => true,
            'allows_patrol_inspection' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn(['is_mandatory', 'allows_patrol_inspection']);
        });
    }
};

