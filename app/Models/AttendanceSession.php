<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AttendanceSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_schedule_id',
        'is_competition',
        'session_type',
        'title',
        'opened_at',
        'closed_at',
        'is_active',
    ];

    protected $casts = [
        'is_competition' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke jadwal sesi lomba
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CompetitionSchedule::class, 'competition_schedule_id');
    }

    /**
     * Relasi ke seluruh presensi yang tercatat pada sesi ini
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'attendance_session_id');
    }

    /**
     * Scope untuk sesi presensi yang sedang aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk sesi presensi kehadiran (bukan lomba)
     */
    public function scopeGeneralAttendance(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('competition_schedule_id')
                ->orWhere('is_competition', false);
        });
    }

    /**
     * Scope untuk sesi presensi pos mata lomba
     */
    public function scopeCompetition(Builder $query): Builder
    {
        return $query->whereNotNull('competition_schedule_id')
            ->where('is_competition', true);
    }

    /**
     * Scope sesi presensi yang berhak diakses oleh user tertentu sesuai peran
     */
    public function scopeAccessibleBy(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isCommittee() && $user->isJudge()) {
            return $query->where(function ($q) use ($user) {
                $q->where(function ($sub) {
                    $sub->whereNull('competition_schedule_id')
                        ->orWhere('is_competition', false);
                })->orWhere(function ($sub) use ($user) {
                    $sub->whereNotNull('competition_schedule_id')
                        ->where('is_competition', true)
                        ->whereHas('schedule.judgeAssignments', fn($ja) => $ja->where('user_id', $user->id));
                });
            });
        }

        if ($user->isJudge()) {
            return $query->whereNotNull('competition_schedule_id')
                ->where('is_competition', true)
                ->whereHas('schedule.judgeAssignments', function ($ja) use ($user) {
                    $ja->where('user_id', $user->id);
                });
        }

        if ($user->isCommittee()) {
            return $query->where(function ($q) {
                $q->whereNull('competition_schedule_id')
                    ->orWhere('is_competition', false);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Memeriksa apakah sesi ini adalah presensi mata lomba
     */
    public function isCompetition(): bool
    {
        if ($this->is_competition !== null) {
            return (bool) $this->is_competition;
        }

        return $this->competition_schedule_id !== null;
    }

    /**
     * Memeriksa apakah sesi ini adalah presensi kehadiran (bukan lomba)
     */
    public function isGeneralAttendance(): bool
    {
        return ! $this->isCompetition();
    }

    /**
     * Memeriksa otorisasi apakah user yang login berhak mengakses/scan sesi presensi ini
     */
    public function canBeAccessedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $isLomba = $this->isCompetition();

        // Akun yang merangkap Panitia & Juri
        if ($user->isCommittee() && $user->isJudge()) {
            if (! $isLomba) {
                return true; // Sebagai Panitia untuk presensi kehadiran
            }

            // Sebagai Juri untuk lomba yang ditunjuk
            return $this->schedule && $this->schedule->judgeAssignments()->where('user_id', $user->id)->exists();
        }

        // Juri: hanya presensi sesuai dengan mata lomba yang ditunjuk
        if ($user->isJudge()) {
            if (! $isLomba) {
                return false;
            }

            return $this->schedule && $this->schedule->judgeAssignments()->where('user_id', $user->id)->exists();
        }

        // Panitia: hanya presensi kehadiran dan bukan lomba
        if ($user->isCommittee()) {
            return ! $isLomba;
        }

        return false;
    }

    /**
     * Label tipe sesi
     */
    public function sessionTypeLabel(): string
    {
        return match ($this->session_type) {
            'participant' => 'Peserta Regu',
            'judge' => 'Aparatur Juri',
            'committee' => 'Panitia',
            'chaperone' => 'Pembina Pendamping',
            default => 'Peserta',
        };
    }

    /**
     * Waktu buka presensi (paling cepat 30 menit sebelum jadwal lomba dimulai)
     */
    public function getEffectiveOpenTime(): ?\Carbon\CarbonInterface
    {
        if ($this->schedule && $this->schedule->start_time) {
            return \Carbon\Carbon::parse($this->schedule->start_time)->subMinutes(30);
        }

        return $this->opened_at ? \Carbon\Carbon::parse($this->opened_at) : null;
    }

    /**
     * Waktu tutup presensi (sesuai waktu jadwal lomba berakhir)
     */
    public function getEffectiveCloseTime(): ?\Carbon\CarbonInterface
    {
        if ($this->schedule && $this->schedule->end_time) {
            return \Carbon\Carbon::parse($this->schedule->end_time);
        }

        return $this->closed_at ? \Carbon\Carbon::parse($this->closed_at) : null;
    }

    /**
     * Cek apakah waktu saat ini sudah memasuki jadwal buka presensi
     */
    public function isScheduleOpen(): bool
    {
        $openTime = $this->getEffectiveOpenTime();
        if (! $openTime) {
            return true;
        }

        return now()->setTimezone('Asia/Jakarta')->greaterThanOrEqualTo($openTime->setTimezone('Asia/Jakarta'));
    }

    /**
     * Cek apakah waktu saat ini telah melewati jadwal tutup lomba
     */
    public function isScheduleClosed(): bool
    {
        $closeTime = $this->getEffectiveCloseTime();
        if (! $closeTime) {
            return false;
        }

        return now()->setTimezone('Asia/Jakarta')->greaterThan($closeTime->setTimezone('Asia/Jakarta'));
    }

    /**
     * Sesi presensi hanya dapat diakses sesuai jadwal buka tutup lomba
     */
    public function isWithinSchedule(): bool
    {
        return $this->isScheduleOpen() && ! $this->isScheduleClosed();
    }

    /**
     * Label status jadwal presensi (menyertakan tanggal dan jam resmi)
     */
    public function scheduleStatusLabel(): string
    {
        if (! $this->is_active) {
            return 'Dinonaktifkan Administrator';
        }

        if (! $this->isScheduleOpen()) {
            $open = $this->getEffectiveOpenTime()?->setTimezone('Asia/Jakarta');
            if (! $open) {
                return 'Belum Dibuka';
            }

            if ($open->isToday()) {
                return 'Belum Dibuka (Hari Ini, Pukul ' . $open->format('H:i') . ' WIB)';
            }

            return 'Belum Dibuka (Buka: ' . $open->isoFormat('D MMM Y, [Pk] HH:mm') . ' WIB)';
        }

        if ($this->isScheduleClosed()) {
            $close = $this->getEffectiveCloseTime()?->setTimezone('Asia/Jakarta');
            if ($close) {
                if ($close->isToday()) {
                    return 'Presensi Telah Ditutup (Hari Ini, ' . $close->format('H:i') . ' WIB)';
                }
                return 'Presensi Telah Ditutup (' . $close->isoFormat('D MMM Y, HH:mm') . ' WIB)';
            }
            return 'Presensi Telah Ditutup';
        }

        return 'Presensi Sedang Dibuka';
    }

    /**
     * Kelas badge warna status jadwal presensi
     */
    public function scheduleStatusBadgeClasses(): string
    {
        if (! $this->is_active) {
            return 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/40';
        }

        if (! $this->isScheduleOpen()) {
            return 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40';
        }

        if ($this->isScheduleClosed()) {
            return 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700';
        }

        return 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40';
    }
}
