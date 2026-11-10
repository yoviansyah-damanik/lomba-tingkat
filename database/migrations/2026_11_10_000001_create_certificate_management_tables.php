<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_certificate_published')->default(false)->after('hide_patrol_identities');
        });

        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('role', 30); // participant, chaperone, committee, judge
            $table->string('template_path')->nullable();
            $table->json('layout')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_certificate_published');
        });
    }
};

