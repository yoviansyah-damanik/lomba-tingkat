<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model jadwal pelaksanaan sesi lomba
 */
class CompetitionSchedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_id',
        'session_name',
        'participant_type',
        'gender_category',
        'arena_location',
        'start_time',
        'end_time',
        'checkin_open_time',
        'checkin_close_time',
        'status',
        'is_published',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'checkin_open_time' => 'datetime',
            'checkin_close_time' => 'datetime',
            'is_published' => 'boolean',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * Boot model: Otomatis hitung waktu presensi dan sinkronkan sesi presensi pos
     */
    protected static function booted(): void
    {
        static::saving(function (CompetitionSchedule $schedule) {
            // Jadwal diubah: pengingat push boleh dikirim ulang untuk waktu yang baru
            if ($schedule->exists && $schedule->isDirty('start_time') && ! $schedule->isDirty('reminder_sent_at')) {
                $schedule->reminder_sent_at = null;
            }

            // Presensi dibuka paling cepat 30 menit sebelum waktu lomba dimulai
            if ($schedule->start_time) {
                $schedule->checkin_open_time = $schedule->start_time->copy()->subMinutes(30);
            }
            // Presensi ditutup saat sesi lomba berakhir
            if ($schedule->end_time) {
                $schedule->checkin_close_time = $schedule->end_time;
            }
        });

        static::created(function (CompetitionSchedule $schedule) {
            $schedule->loadMissing('competition');
            $competitionTitle = $schedule->competition?->title ?: 'Lomba';
            $openTime = $schedule->checkin_open_time ?: ($schedule->start_time ? $schedule->start_time->copy()->subMinutes(30) : now());
            $closeTime = $schedule->checkin_close_time ?: $schedule->end_time;

            $schedule->attendanceSessions()->firstOrCreate(
                ['competition_schedule_id' => $schedule->id],
                [
                    'session_type' => 'participant',
                    'title' => 'Presensi: ' . $competitionTitle . ' (' . $schedule->session_name . ')',
                    'is_competition' => true,
                    'opened_at' => $openTime,
                    'closed_at' => $closeTime,
                    'is_active' => true,
                ]
            );
        });

        static::saved(function (CompetitionSchedule $schedule) {
            if ($schedule->start_time && $schedule->end_time) {
                $openTime = $schedule->start_time->copy()->subMinutes(30);
                $closeTime = $schedule->end_time;

                foreach ($schedule->attendanceSessions as $session) {
                    $session->update([
                        'opened_at' => $openTime,
                        'closed_at' => $closeTime,
                    ]);
                }
            }
        });
    }

    /**
     * Scope untuk jadwal yang telah dipublikasikan
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('start_time');
    }

    /**
     * Relasi ke mata lomba
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Relasi penugasan juri
     */
    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }

    /**
     * Relasi ke seluruh juri yang bertugas
     */
    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'judge_assignments', 'competition_schedule_id', 'user_id')
            ->withPivot(['role', 'is_confirmed', 'notes'])
            ->withTimestamps();
    }

    /**
     * Relasi ke sesi presensi pos lomba
     */
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Relasi ke seluruh penilaian juri pada jadwal ini
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Helper sesi presensi yang sedang aktif
     */
    public function activeAttendanceSession(): ?AttendanceSession
    {
        return $this->attendanceSessions()->where('is_active', true)->first();
    }

    /**
     * Mendapatkan tipe kepesertaan sesi jadwal (default ke competition jika null)
     */
    public function participantType(): string
    {
        return $this->participant_type ?: ($this->competition?->participant_type ?? 'patrol');
    }

    /**
     * Helper cek apakah jadwal bertipe individu
     */
    public function isIndividual(): bool
    {
        return $this->participantType() === 'individual';
    }

    /**
     * Helper cek apakah jadwal bertipe kelompok (beberapa orang)
     */
    public function isGroup(): bool
    {
        return $this->participantType() === 'group';
    }

    /**
     * Helper cek apakah jadwal bertipe regu utuh
     */
    public function isPatrol(): bool
    {
        return in_array($this->participantType(), ['patrol', 'full_patrol']);
    }

    /**
     * Label representatif format peserta jadwal
     */
    public function participantTypeLabel(): string
    {
        if ($this->isIndividual()) {
            return 'Individu (1 Peserta)';
        }

        if ($this->isGroup()) {
            if ($this->competition) {
                return $this->competition->participantRangeLabel();
            }
            return 'Kelompok Peserta';
        }

        if ($this->competition) {
            return $this->competition->participantRangeLabel();
        }

        return 'Regu Utuh Penggalang';
    }

    /**
     * Kelas CSS badge tipe kepesertaan jadwal
     */
    public function participantTypeBadgeClasses(): string
    {
        if ($this->isIndividual()) {
            return 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800/40';
        }

        if ($this->isGroup()) {
            return 'bg-indigo-100 text-indigo-800 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800/40';
        }

        return 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/40';
    }

    /**
     * Waktu buka presensi resmi (paling cepat 30 menit sebelum jadwal lomba dimulai)
     */
    public function getAttendanceOpenTimeAttribute(): ?\Carbon\CarbonInterface
    {
        return $this->start_time ? \Carbon\Carbon::parse($this->start_time)->subMinutes(30) : null;
    }

    /**
     * Waktu tutup presensi resmi (saat sesi lomba berakhir)
     */
    public function getAttendanceCloseTimeAttribute(): ?\Carbon\CarbonInterface
    {
        return $this->end_time ? \Carbon\Carbon::parse($this->end_time) : null;
    }

    /**
     * Helper cek apakah presensi saat ini sedang terbuka (30 menit sebelum mulai hingga batas akhir)
     */
    public function isAttendanceOpen(): bool
    {
        if (! $this->start_time || ! $this->end_time) {
            return true;
        }

        $now = now()->setTimezone('Asia/Jakarta');
        return $now->between($this->attendance_open_time, $this->attendance_close_time);
    }

    /**
     * Helper cek apakah presensi belum dibuka (sebelum H-30 menit)
     */
    public function isAttendanceNotOpenYet(): bool
    {
        if (! $this->start_time) {
            return false;
        }

        $now = now()->setTimezone('Asia/Jakarta');
        return $now->lessThan($this->attendance_open_time);
    }

    /**
     * Helper cek apakah presensi telah ditutup (setelah lomba berakhir)
     */
    public function isAttendanceClosed(): bool
    {
        if (! $this->end_time) {
            return false;
        }

        $now = now()->setTimezone('Asia/Jakarta');
        return $now->greaterThan($this->attendance_close_time);
    }

    /**
     * Alias backward compatibility
     */
    public function isCheckInOpen(): bool
    {
        return $this->isAttendanceOpen();
    }

    /**
     * Sesi presensi utama untuk jadwal ini
     */
    public function primaryAttendanceSession(): ?AttendanceSession
    {
        return $this->attendanceSessions->first();
    }

    /**
     * Cek apakah saat ini presensi aktif dan siap dilakukan scan:
     * Otomatis terbuka 30 menit sebelum waktu lomba hingga selesai,
     * kecuali jika ditutup secara manual oleh admin saat waktu lomba.
     */
    public function isAttendanceScanningReady(): bool
    {
        $session = $this->primaryAttendanceSession();
        $isManuallyActive = $session ? $session->is_active : true;

        return $this->isAttendanceOpen() && $isManuallyActive;
    }

    /**
     * Label status sesi presensi untuk kolom tabel Jadwal Lomba
     */
    public function attendanceSessionStatusLabel(): string
    {
        $session = $this->primaryAttendanceSession();
        $isManuallyActive = $session ? $session->is_active : true;

        if ($this->isAttendanceNotOpenYet()) {
            $open = $this->attendance_open_time?->setTimezone('Asia/Jakarta');
            $timeStr = $open ? ($open->isToday() ? 'Pk. ' . $open->format('H:i') : $open->isoFormat('D MMM, HH:mm')) . ' WIB' : '-';
            return 'Belum Dibuka (Otomatis Buka ' . $timeStr . ')';
        }

        if ($this->isAttendanceClosed()) {
            return 'Presensi Ditutup (Sesi Selesai)';
        }

        // Berada di rentang waktu lomba (H-30m s/d Selesai)
        if (! $isManuallyActive) {
            return 'Ditutup Manual Saat Waktu Lomba';
        }

        return 'Aktif Scan (Otomatis Terbuka)';
    }

    /**
     * Kelas badge warna status sesi presensi untuk kolom tabel Jadwal Lomba
     */
    public function attendanceSessionBadgeClasses(): string
    {
        $session = $this->primaryAttendanceSession();
        $isManuallyActive = $session ? $session->is_active : true;

        if ($this->isAttendanceNotOpenYet()) {
            return 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40';
        }

        if ($this->isAttendanceClosed()) {
            return 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700';
        }

        if (! $isManuallyActive) {
            return 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/40';
        }

        return 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40';
    }

    /**
     * Badge status jadwal
     */
    public function statusBadge(): string
    {
        return match ($this->status) {
            'in_progress' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 animate-pulse',
            'completed' => 'bg-zinc-100 text-zinc-800 border-zinc-300 dark:bg-zinc-800 dark:text-zinc-300',
            'rescheduled' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300',
            'cancelled' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-950/60 dark:text-red-300',
            default => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300',
        };
    }

    /**
     * Label status jadwal dalam Bahasa Indonesia
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'in_progress' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'rescheduled' => 'Dijadwal Ulang',
            'cancelled' => 'Dibatalkan',
            default => 'Terjadwal',
        };
    }

    /**
     * Kategori gender efektif (memperhitungkan penugasan jadwal atau turunan lomba)
     */
    public function effectiveGenderCategory(): string
    {
        if ($this->gender_category && in_array($this->gender_category, ['boys', 'girls'])) {
            return $this->gender_category;
        }

        return $this->competition?->category ?: 'all';
    }

    /**
     * Helper cek apakah jadwal untuk Putra
     */
    public function isBoys(): bool
    {
        return $this->effectiveGenderCategory() === 'boys';
    }

    /**
     * Helper cek apakah jadwal untuk Putri
     */
    public function isGirls(): bool
    {
        return $this->effectiveGenderCategory() === 'girls';
    }

    /**
     * Helper cek apakah jadwal terbuka untuk Putra & Putri
     */
    public function isAllGender(): bool
    {
        return $this->effectiveGenderCategory() === 'all';
    }

    /**
     * Label kategori gender sesi jadwal
     */
    public function genderLabel(): string
    {
        return match ($this->effectiveGenderCategory()) {
            'boys' => 'Penggalang Putra',
            'girls' => 'Penggalang Putri',
            default => 'Putra & Putri',
        };
    }

    /**
     * Badge CSS class kategori gender sesi jadwal
     */
    public function genderBadgeClasses(): string
    {
        return match ($this->effectiveGenderCategory()) {
            'boys' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'girls' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
        };
    }
}
