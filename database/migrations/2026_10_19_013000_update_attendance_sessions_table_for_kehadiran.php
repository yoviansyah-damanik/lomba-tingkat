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
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->uuid('competition_schedule_id')->nullable()->change();
            $table->boolean('is_competition')->default(true)->after('competition_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->uuid('competition_schedule_id')->nullable(false)->change();
            $table->dropColumn('is_competition');
        });
    }
};
