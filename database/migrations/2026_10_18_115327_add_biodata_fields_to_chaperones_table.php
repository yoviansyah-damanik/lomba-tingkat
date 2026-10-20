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
        Schema::table('chaperones', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('religion')->nullable()->after('birth_date');
            $table->string('blood_type', 5)->nullable()->after('religion');
            $table->text('home_address')->nullable()->after('national_identity_number');
            $table->string('city')->default('Padangsidimpuan')->after('home_address');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->string('home_phone', 30)->nullable()->after('postal_code');
            $table->json('formal_education')->nullable()->after('photo_path');
            $table->json('scout_experiences')->nullable()->after('formal_education');
            $table->json('scout_trainings')->nullable()->after('scout_experiences');
            $table->json('other_trainings')->nullable()->after('scout_trainings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chaperones', function (Blueprint $table) {
            $table->dropColumn([
                'birth_place',
                'birth_date',
                'religion',
                'blood_type',
                'home_address',
                'city',
                'postal_code',
                'home_phone',
                'formal_education',
                'scout_experiences',
                'scout_trainings',
                'other_trainings',
            ]);
        });
    }
};
