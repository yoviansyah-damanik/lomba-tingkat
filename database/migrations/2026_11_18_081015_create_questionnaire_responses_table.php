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
        Schema::create('questionnaire_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('role', 30); // committee, judge, both
            $table->string('duty_assignment')->nullable(); // Pos penugasan atau mata lomba

            // Rating skala 1 s.d. 5 bintang
            $table->unsignedTinyInteger('rating_overall'); // Kepuasan umum aplikasi
            $table->unsignedTinyInteger('rating_ui_ux'); // Kemudahan antarmuka dan navigasi HP/laptop
            $table->unsignedTinyInteger('rating_features'); // Efektivitas fitur penjurian/presensi
            $table->unsignedTinyInteger('rating_speed'); // Kecepatan dan kestabilan sistem di lapangan
            $table->unsignedTinyInteger('rating_transparency'); // Objektivitas dan transparansi rekapitulasi nilai

            // Pertanyaan pilihan ganda
            $table->string('device_used', 50); // Smartphone Android, iPhone (iOS), Tablet/iPad, Laptop
            $table->string('efficiency_level', 50); // Sangat efisien, Cukup membantu, Sama saja, Lebih rumit
            $table->string('main_obstacle', 50); // Tidak ada kendala, Sinyal internet buper, Baterai, Keterbacaan layar, Rubrik rinci
            $table->string('most_useful_feature', 50); // Cockpit Penjurian, Blind Scoring, Presensi QR, Leaderboard, Cetak PDF
            $table->string('rubric_clarity', 50)->nullable(); // Sangat jelas, Cukup jelas, Kurang jelas
            $table->string('future_readiness', 50); // Wajib dilanjutkan, Layak dengan penyempurnaan, Perlu evaluasi, Kembali manual

            // Pertanyaan terbuka & rekomendasi
            $table->text('recommended_features')->nullable(); // Fitur baru yang perlu dikembangkan
            $table->text('experience_feedback')->nullable(); // Kesan dan pengalaman lapangan
            $table->text('general_suggestions')->nullable(); // Kritik dan saran tambahan

            $table->timestamps();

            // Satu akun hanya mengisi 1 kuesioner per kegiatan (bisa diperbarui)
            $table->unique(['user_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_responses');
    }
};
