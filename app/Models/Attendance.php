<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'attendance_session_id',
        'entity_type',
        'participant_id',
        'patrol_id',
        'chaperone_id',
        'user_id',
        'scanned_by_user_id',
        'scanned_at',
        'status',
        'notes',
        'device_info',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * Sesi presensi terkait
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    /**
     * Alias relasi sesi presensi
     */
    public function attendanceSession(): BelongsTo
    {
        return $this->session();
    }

    /**
     * Relasi ke regu peserta
     */
    public function patrol(): BelongsTo
    {
        return $this->belongsTo(Patrol::class);
    }

    /**
     * Relasi ke peserta jika tipe entitas adalah peserta
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Relasi ke pembina pendamping jika tipe entitas adalah pembina
     */
    public function chaperone(): BelongsTo
    {
        return $this->belongsTo(Chaperone::class);
    }

    /**
     * Relasi ke pengguna (panitia / dewan juri)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Petugas scanner yang melakukan validasi presensi
     */
    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    /**
     * Dapatkan model entitas yang hadir
     */
    public function getEntityAttribute(): mixed
    {
        return match ($this->entity_type) {
            'participant' => $this->participant,
            'chaperone' => $this->chaperone,
            'user' => $this->user,
            default => null,
        };
    }

    /**
     * Nama entitas
     */
    public function getEntityNameAttribute(): string
    {
        if ($this->entity_type === 'participant' && $this->participant) {
            return $this->participant->full_name;
        }

        if ($this->entity_type === 'chaperone' && $this->chaperone) {
            return $this->chaperone->full_name;
        }

        if ($this->entity_type === 'user' && $this->user) {
            return $this->user->name;
        }

        return 'Entitas Tidak Diketahui';
    }

    /**
     * ID resmi (ID-XXXXXXXX)
     */
    public function getEntityIdNumberAttribute(): string
    {
        if ($this->entity_type === 'participant' && $this->participant) {
            return $this->participant->id_number ?: 'ID-PESERTA';
        }

        if ($this->entity_type === 'chaperone' && $this->chaperone) {
            return $this->chaperone->id_number ?: 'ID-PENDAMPING';
        }

        if ($this->entity_type === 'user' && $this->user) {
            return $this->user->id_number ?: 'ID-APARATUR';
        }

        return '-';
    }

    /**
     * Peran atau jabatan entitas
     */
    public function getEntityRoleAttribute(): string
    {
        if ($this->entity_type === 'participant' && $this->participant) {
            return match ($this->participant->role_in_patrol?->value) {
                'patrol_leader' => 'Pemimpin Regu (PINRU)',
                'assistant_patrol_leader' => 'Wapinru',
                default => 'Anggota Regu',
            };
        }

        if ($this->entity_type === 'chaperone' && $this->chaperone) {
            return 'Pembina Pendamping (' . ($this->chaperone->isFemale() ? 'Putri' : 'Putra') . ')';
        }

        if ($this->entity_type === 'user' && $this->user) {
            return $this->user->position ?: ($this->user->roles->pluck('label')->join(' & ') ?: 'Aparatur');
        }

        return '-';
    }

    /**
     * Gugus Depan / Instansi entitas
     */
    public function getEntityGroupAttribute(): string
    {
        if ($this->entity_type === 'participant' && $this->participant) {
            $scoutGroup = $this->participant->patrol?->scoutGroup;
            return $scoutGroup ? ($scoutGroup->school_name . ' (' . $this->participant->patrol?->patrol_name . ')') : '-';
        }

        if ($this->entity_type === 'chaperone' && $this->chaperone) {
            return $this->chaperone->scoutGroup?->school_name ?: '-';
        }

        if ($this->entity_type === 'user' && $this->user) {
            return $this->user->institution ?: 'Kwarcab Padangsidimpuan';
        }

        return '-';
    }

    /**
     * Foto atau avatar URL
     */
    public function getEntityPhotoUrlAttribute(): ?string
    {
        if ($this->entity_type === 'participant' && $this->participant?->photo_path) {
            return asset('storage/' . $this->participant->photo_path);
        }

        if ($this->entity_type === 'chaperone' && $this->chaperone?->photo_path) {
            return asset('storage/' . $this->chaperone->photo_path);
        }

        if ($this->entity_type === 'user' && $this->user?->avatar_path) {
            return asset('storage/' . $this->user->avatar_path);
        }

        return null;
    }

    /**
     * Label status kehadiran
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'present' => 'Hadir Tepat Waktu',
            'late' => 'Terlambat',
            'excused' => 'Izin / Dispensasi',
            'absent' => 'Tidak Hadir',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge CSS class status kehadiran
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'present' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'late' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'excused' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'absent' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-zinc-50 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
        };
    }
}
