<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model respon kuisioner pengalaman Panitia dan Dewan Juri
 */
class QuestionnaireResponse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'event_id',
        'role',
        'duty_assignment',
        'rating_overall',
        'rating_ui_ux',
        'rating_features',
        'rating_speed',
        'rating_transparency',
        'device_used',
        'efficiency_level',
        'main_obstacle',
        'most_useful_feature',
        'rubric_clarity',
        'future_readiness',
        'recommended_features',
        'experience_feedback',
        'general_suggestions',
    ];

    protected function casts(): array
    {
        return [
            'rating_overall' => 'integer',
            'rating_ui_ux' => 'integer',
            'rating_features' => 'integer',
            'rating_speed' => 'integer',
            'rating_transparency' => 'integer',
        ];
    }

    public const DEVICES = [
        'android' => 'Smartphone Android',
        'ios' => 'iPhone (iOS)',
        'tablet' => 'Tablet / iPad',
        'laptop' => 'Laptop / Komputer',
    ];

    public const EFFICIENCY_LEVELS = [
        'very_efficient' => 'Sangat jauh lebih cepat & efisien (hemat waktu hitung/rekap)',
        'helpful' => 'Cukup membantu mengurangi beban operasional',
        'neutral' => 'Sama saja dengan sistem manual kertas',
        'complicated' => 'Lebih rumit dibanding sistem manual',
    ];

    public const OBSTACLES = [
        'none' => 'Tidak ada kendala (lancar)',
        'signal' => 'Sinyal / jaringan internet seluler di buper tidak stabil',
        'battery' => 'Daya baterai perangkat cepat berkurang di pos',
        'screen_glare' => 'Keterbacaan layar di bawah terik matahari siang',
        'rubric_detail' => 'Kriteria rubrik terlalu rinci dan memakan waktu input',
    ];

    public const MOST_USEFUL_FEATURES = [
        'scoring_cockpit' => 'Cockpit Penjurian & Rubrik Digital Real-time',
        'blind_scoring' => 'Penilaian Anonim / Blind Scoring berbasis NPP',
        'qr_attendance' => 'Pemindai Presensi QR Code & ID Card Digital',
        'leaderboard' => 'Papan Klasemen & Perolehan Prestasi Real-time',
        'pdf_reports' => 'Penerbitan Berita Acara & Dokumen PDF Otomatis',
    ];

    public const RUBRIC_CLARITY_OPTIONS = [
        'very_clear' => 'Sangat jelas dan mudah dipahami',
        'clear_enough' => 'Cukup jelas, butuh sedikit konfirmasi juknis',
        'less_clear' => 'Kurang jelas dan butuh panduan lebih lanjut',
    ];

    public const READINESS_OPTIONS = [
        'mandatory' => 'Sangat layak & wajib dilanjutkan untuk LT berikutnya',
        'continue_with_improvements' => 'Layak dilanjutkan dengan penyempurnaan fitur',
        'need_evaluation' => 'Perlu evaluasi menyeluruh sebelum dipakai lagi',
        'back_to_manual' => 'Lebih baik kembali ke format manual kertas',
    ];

    public const RATING_DESCRIPTIONS = [
        1 => 'Sangat Buruk',
        2 => 'Buruk',
        3 => 'Cukup',
        4 => 'Baik',
        5 => 'Sangat Baik',
    ];

    /**
     * Relasi ke pengguna responden
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke kegiatan induk
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Menghitung nilai rata-rata dari 5 aspek rating bintang
     */
    public function getAverageRatingAttribute(): float
    {
        $sum = $this->rating_overall + $this->rating_ui_ux + $this->rating_features + $this->rating_speed + $this->rating_transparency;

        return round($sum / 5, 2);
    }

    /**
     * Label peran responden
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'committee' => 'Panitia Pelaksana',
            'judge' => 'Dewan Juri',
            'both' => 'Panitia & Dewan Juri',
            default => 'Aparatur Kegiatan',
        };
    }
}
