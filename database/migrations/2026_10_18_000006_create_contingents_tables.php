<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi entitas kontingen: scout_groups, patrols, participants, chaperones
     */
    public function up(): void
    {
        // 1. Tabel Gugus Depan (Pangkalan Sekolah)
        Schema::create('scout_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('registration_code')->unique();
            $table->string('school_name');
            $table->string('group_number');
            $table->text('address');
            $table->string('contact_person');
            $table->string('contact_phone');
            $table->string('verification_status')->default('draft');
            $table->text('revision_notes')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->uuid('invitation_id')->nullable()->index();
            $table->string('registered_ip', 45)->nullable();
            $table->string('registered_user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['verification_status', 'created_at']);
        });

        // 2. Tabel Regu Penggalang (Patrols)
        Schema::create('patrols', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scout_group_id')->constrained('scout_groups')->cascadeOnDelete();
            $table->string('patrol_name');
            $table->string('gender_category'); // boys, girls
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['scout_group_id', 'gender_category']);
        });

        // 3. Tabel Peserta (Participants)
        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patrol_id')->constrained('patrols')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('national_student_id')->nullable(); // NISN
            $table->string('scout_id')->nullable(); // KTA Pramuka
            $table->string('role_in_patrol')->default('member'); // patrol_leader, assistant_patrol_leader, member
            $table->string('gender'); // male, female
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('blood_type', 5)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->dateTime('qr_generated_at')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['patrol_id', 'role_in_patrol']);
        });

        // 4. Tabel Pembina Pendamping (Chaperones)
        Schema::create('chaperones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scout_group_id')->constrained('scout_groups')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('scout_id')->nullable();
            $table->string('national_identity_number')->nullable(); // NIK
            $table->string('gender');
            $table->string('phone_number');
            $table->string('photo_path')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        // 5. Bindamping pendamping per regu: satu bindamping dapat mendampingi beberapa regu sekategori
        Schema::table('patrols', function (Blueprint $table) {
            $table->foreignUuid('chaperone_id')->nullable()->constrained('chaperones')->nullOnDelete();
        });
    }

    /**
     * Membalikkan migrasi
     */
    public function down(): void
    {
        Schema::dropIfExists('chaperones');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('patrols');
        Schema::dropIfExists('scout_groups');
    }
};
