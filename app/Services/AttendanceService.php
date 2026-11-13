<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service untuk pemrosesan presensi multi-entitas (Peserta, Pembina, Aparatur) via QR Scanner dan Manual
 */
class AttendanceService
{
    public function __construct(
        protected QrCodeService $qrCodeService
    ) {}

    /**
     * Memproses pemindaian token QR pada suatu sesi presensi
     */
    public function recordScan(
        AttendanceSession $session,
        string $token,
        ?User $scannedBy = null,
        ?string $deviceInfo = null
    ): array {
        // 1. Validasi keaktifan sesi presensi
        if (! $session->is_active) {
            return [
                'success' => false,
                'code' => 'SESSION_INACTIVE',
                'message' => 'Sesi presensi "'.$session->title.'" sedang ditutup atau belum diaktifkan.',
            ];
        }

        // 1.b Validasi kesesuaian jadwal buka dan tutup lomba
        $session->loadMissing('schedule');
        if (! $session->isScheduleOpen()) {
            $openTime = $session->getEffectiveOpenTime();
            $formattedTime = $openTime ? $openTime->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y [pukul] HH:mm') : '';

            return [
                'success' => false,
                'code' => 'SCHEDULE_NOT_OPEN',
                'message' => "Sesi presensi belum dibuka. Presensi dibuka paling cepat 30 menit sebelum waktu lomba dimulai ({$formattedTime} WIB).",
            ];
        }

        if ($session->isScheduleClosed()) {
            $closeTime = $session->getEffectiveCloseTime();
            $formattedTime = $closeTime ? $closeTime->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y [pukul] HH:mm') : '';

            return [
                'success' => false,
                'code' => 'SCHEDULE_CLOSED',
                'message' => "Sesi presensi telah ditutup. Jadwal sesi lomba ini telah berakhir pada {$formattedTime} WIB.",
            ];
        }

        // 2. Bersihkan token (jika berupa URL lengkap, ambil segmen terakhir)
        $cleanToken = trim($token);
        if (str_contains($cleanToken, '/verify/')) {
            $parts = explode('/verify/', $cleanToken);
            $cleanToken = end($parts);
        }

        // 3. Selesaikan entitas dari token
        $resolved = $this->qrCodeService->resolveToken($cleanToken);
        if (! $resolved) {
            return [
                'success' => false,
                'code' => 'INVALID_TOKEN',
                'message' => 'Token QR tidak valid atau kartu identitas digital tidak ditemukan dalam sistem.',
            ];
        }

        $entity = $resolved['entity'];
        $entityType = match ($resolved['type']) {
            'participant' => 'participant',
            'chaperone' => 'chaperone',
            default => 'user',
        };

        $session->loadMissing(['schedule.competition']);
        $schedule = $session->schedule;
        $competition = $schedule?->competition;
        $patrol = null;
        $assignment = null;

        // 4. Validasi aturan entitas untuk Sesi Lomba vs Sesi Kehadiran Umum
        if ($session->isCompetition()) {
            // A. Peserta Lomba
            if ($entityType === 'participant') {
                /** @var Participant $participant */
                $participant = $entity;
                $patrol = $participant->patrol;

                if (! $patrol) {
                    return [
                        'success' => false,
                        'code' => 'NO_PATROL',
                        'message' => "Peserta {$participant->full_name} belum terdaftar dalam regu manapun.",
                        'attendee' => $this->formatAttendeePayload(null, $resolved),
                    ];
                }

                // Validasi Kategori Golongan (Putra vs Putri)
                if ($competition) {
                    $effectiveGender = $schedule?->effectiveGenderCategory();
                    if ($effectiveGender === 'boys' && $patrol->isGirls()) {
                        return [
                            'success' => false,
                            'code' => 'CATEGORY_MISMATCH',
                            'message' => "Sesi jadwal lomba ini diperuntukkan khusus bagi Regu Putra. {$participant->full_name} terdaftar pada Regu Putri ({$patrol->patrol_name}).",
                            'attendee' => $this->formatAttendeePayload(null, $resolved),
                        ];
                    }
                    if ($effectiveGender === 'girls' && $patrol->isBoys()) {
                        return [
                            'success' => false,
                            'code' => 'CATEGORY_MISMATCH',
                            'message' => "Sesi jadwal lomba ini diperuntukkan khusus bagi Regu Putri. {$participant->full_name} terdaftar pada Regu Putra ({$patrol->patrol_name}).",
                            'attendee' => $this->formatAttendeePayload(null, $resolved),
                        ];
                    }
                }

                // Mencegah double-scan perorangan pada sesi lomba
                $existing = Attendance::where('attendance_session_id', $session->id)
                    ->where('participant_id', $participant->id)
                    ->first();

                if ($existing) {
                    return [
                        'success' => false,
                        'code' => 'ALREADY_SCANNED',
                        'message' => 'Presensi atas nama '.$resolved['name'].' sudah tercatat pada pukul '.$existing->scanned_at->format('H:i:s').' WIB.',
                        'attendance' => $existing,
                        'attendee' => $this->formatAttendeePayload($existing, $resolved),
                    ];
                }

                // 1) Lomba Kategori Regu Utuh: Wajib dipresensi oleh PINRU / WAPINRU (1 regu 1 kali)
                if ($schedule?->isPatrol()) {
                    $isLeader = $participant->isLeader() || $participant->isAssistantLeader();
                    if (! $isLeader) {
                        return [
                            'success' => false,
                            'code' => 'LEADER_REQUIRED',
                            'message' => "Presensi lomba kategori Regu Utuh wajib dilakukan oleh Pemimpin Regu (PINRU) atau Wakil Pemimpin Regu (WAPINRU). {$participant->full_name} terdaftar sebagai Anggota Regu.",
                            'attendee' => $this->formatAttendeePayload(null, $resolved),
                        ];
                    }

                    $existingPatrol = Attendance::where('attendance_session_id', $session->id)
                        ->where('patrol_id', $patrol->id)
                        ->first();

                    if ($existingPatrol) {
                        return [
                            'success' => false,
                            'code' => 'ALREADY_SCANNED',
                            'message' => "Regu Utuh {$patrol->patrol_name} (".($patrol->scoutGroup?->school_name ?? '-').') sudah tercatat presensi sebelumnya pada pukul '.$existingPatrol->scanned_at->format('H:i:s')." WIB oleh {$existingPatrol->entity_name}.",
                            'attendance' => $existingPatrol,
                            'attendee' => $this->formatAttendeePayload($existingPatrol, $resolved),
                        ];
                    }
                }

                // 2) Lomba Kategori Individu: Hanya bisa diabsen 1 orang perwakilan per regu
                elseif ($schedule?->isIndividual()) {
                    $existingPatrol = Attendance::where('attendance_session_id', $session->id)
                        ->where('patrol_id', $patrol->id)
                        ->first();

                    if ($existingPatrol) {
                        return [
                            'success' => false,
                            'code' => 'ALREADY_SCANNED',
                            'message' => "Perwakilan dari Regu {$patrol->patrol_name} (".($patrol->scoutGroup?->school_name ?? '-').") sudah tercatat presensi atas nama {$existingPatrol->entity_name} pada pukul ".$existingPatrol->scanned_at->format('H:i:s').' WIB.',
                            'attendance' => $existingPatrol,
                            'attendee' => $this->formatAttendeePayload($existingPatrol, $resolved),
                        ];
                    }
                }

                // 3) Lomba Kategori Kelompok: Dibatasi maksimal n orang per regu
                elseif ($schedule?->isGroup()) {
                    $maxParticipants = $competition?->max_participants ?: ($competition?->min_participants ?: 8);
                    $existingGroupCount = Attendance::where('attendance_session_id', $session->id)
                        ->where('patrol_id', $patrol->id)
                        ->count();

                    if ($existingGroupCount >= $maxParticipants) {
                        return [
                            'success' => false,
                            'code' => 'GROUP_QUOTA_EXCEEDED',
                            'message' => "Kuota peserta kelompok untuk Regu {$patrol->patrol_name} sudah terpenuhi ({$existingGroupCount}/{$maxParticipants} peserta). Seluruh anggota kelompok yang ditugaskan sudah hadir.",
                            'attendee' => $this->formatAttendeePayload(null, $resolved),
                        ];
                    }
                }
            }

            // B. Dewan Juri & Panitia (Tipe Entitas User)
            elseif ($entityType === 'user') {
                /** @var User $userEntity */
                $userEntity = $entity;

                // Dewan juri hanya bisa presensi sesuai dengan jadwal lomba yang ditunjuk
                $assignment = $schedule ? $schedule->judgeAssignments()->where('user_id', $userEntity->id)->first() : null;

                if (! $assignment) {
                    if ($userEntity->isJudge()) {
                        return [
                            'success' => false,
                            'code' => 'JUDGE_NOT_ASSIGNED',
                            'message' => "Dewan Juri {$resolved['name']} tidak ditugaskan pada jadwal lomba ini.",
                            'attendee' => $this->formatAttendeePayload(null, $resolved),
                        ];
                    }

                    if ($userEntity->isCommittee()) {
                        return [
                            'success' => false,
                            'code' => 'COMMITTEE_NOT_ELIGIBLE',
                            'message' => 'Panitia pelaksana tidak memerlukan absensi lomba.',
                            'attendee' => $this->formatAttendeePayload(null, $resolved),
                        ];
                    }

                    return [
                        'success' => false,
                        'code' => 'USER_NOT_ELIGIBLE',
                        'message' => "Pengguna {$resolved['name']} tidak terdaftar sebagai peserta atau dewan juri pada lomba ini.",
                        'attendee' => $this->formatAttendeePayload(null, $resolved),
                    ];
                }

                // Dewan juri yang ditugaskan: cek apakah sudah presensi
                $existing = Attendance::where('attendance_session_id', $session->id)
                    ->where('user_id', $userEntity->id)
                    ->first();

                if ($existing) {
                    return [
                        'success' => false,
                        'code' => 'ALREADY_SCANNED',
                        'message' => "Presensi Dewan Juri {$resolved['name']} sudah tercatat pada pukul ".$existing->scanned_at->format('H:i:s').' WIB.',
                        'attendance' => $existing,
                        'attendee' => $this->formatAttendeePayload($existing, $resolved),
                    ];
                }
            }

            // C. Pembina Pendamping tidak mengikuti lomba
            elseif ($entityType === 'chaperone') {
                return [
                    'success' => false,
                    'code' => 'CHAPERONE_NOT_ELIGIBLE',
                    'message' => 'Pembina Pendamping tidak memerlukan absensi lomba.',
                    'attendee' => $this->formatAttendeePayload(null, $resolved),
                ];
            }
        } else {
            // Sesi Kehadiran Umum (Bukan Lomba): Validasi kesesuaian session_type
            if ($session->session_type === 'committee') {
                if ($entityType !== 'user' || ! $entity->isCommittee()) {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Panitia Pelaksana.',
                        'attendee' => $this->formatAttendeePayload(null, $resolved),
                    ];
                }
            } elseif ($session->session_type === 'judge') {
                if ($entityType !== 'user' || ! $entity->isJudge()) {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Dewan Juri.',
                        'attendee' => $this->formatAttendeePayload(null, $resolved),
                    ];
                }
            } elseif ($session->session_type === 'chaperone') {
                if ($entityType !== 'chaperone') {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Pembina Pendamping.',
                        'attendee' => $this->formatAttendeePayload(null, $resolved),
                    ];
                }
            } elseif ($session->session_type === 'participant') {
                if ($entityType !== 'participant') {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Peserta Lomba.',
                        'attendee' => $this->formatAttendeePayload(null, $resolved),
                    ];
                }
            }

            // Cek duplikasi perorangan untuk sesi umum
            $existing = Attendance::where('attendance_session_id', $session->id)
                ->where(function ($query) use ($entityType, $entity) {
                    if ($entityType === 'participant') {
                        $query->where('participant_id', $entity->id);
                    } elseif ($entityType === 'chaperone') {
                        $query->where('chaperone_id', $entity->id);
                    } else {
                        $query->where('user_id', $entity->id);
                    }
                })
                ->first();

            if ($existing) {
                return [
                    'success' => false,
                    'code' => 'ALREADY_SCANNED',
                    'message' => 'Presensi atas nama '.$resolved['name'].' sudah tercatat pada pukul '.$existing->scanned_at->format('H:i:s').' WIB.',
                    'attendance' => $existing,
                    'attendee' => $this->formatAttendeePayload($existing, $resolved),
                ];
            }
        }

        // 5. Tentukan catatan dan pesan sukses presensi
        $notes = null;
        $customMessage = null;
        if ($entityType === 'participant') {
            if ($schedule?->isPatrol()) {
                $notes = 'Presensi Regu Utuh oleh '.($entity->isLeader() ? 'PINRU' : 'WAPINRU');
                $customMessage = 'Presensi Regu Utuh '.($patrol?->patrol_name ?? '-').' ('.($entity->isLeader() ? 'PINRU' : 'WAPINRU').': '.$resolved['name'].') berhasil dicatat!';
            } elseif ($schedule?->isGroup()) {
                $maxParticipants = $competition?->max_participants ?: ($competition?->min_participants ?: 8);
                $newCount = Attendance::where('attendance_session_id', $session->id)->where('patrol_id', $patrol?->id)->count() + 1;
                $notes = "Peserta Kelompok ({$newCount}/{$maxParticipants}) • Regu ".($patrol?->patrol_name ?? '-');
                if ($newCount >= $maxParticipants) {
                    $customMessage = "Presensi {$resolved['name']} berhasil dicatat! Kelompok Regu ".($patrol?->patrol_name ?? '-')." LENGKAP ({$newCount}/{$maxParticipants} peserta).";
                } else {
                    $remaining = $maxParticipants - $newCount;
                    $customMessage = "Presensi {$resolved['name']} berhasil dicatat ({$newCount}/{$maxParticipants} peserta kelompok). Sisa {$remaining} peserta kelompok wajib presensi.";
                }
            } elseif ($schedule?->isIndividual()) {
                $notes = 'Perwakilan Lomba Individu: '.($patrol?->patrol_name ?? '-');
                $customMessage = 'Presensi lomba individu perwakilan Regu '.($patrol?->patrol_name ?? '-').' atas nama '.$resolved['name'].' berhasil dicatat!';
            }
        } elseif ($entityType === 'user') {
            if ($session->isCompetition()) {
                $roleText = $assignment?->roleLabel() ?: 'Dewan Juri';
                $notes = 'Dewan Juri: '.$roleText;
                $customMessage = "Presensi Dewan Juri {$resolved['name']} berhasil dicatat!";
            } elseif ($session->session_type === 'committee') {
                $notes = 'Presensi Panitia: '.($entity->committee_position ?: $entity->position ?: 'Panitia Pelaksana');
                $customMessage = "Presensi Panitia {$resolved['name']} berhasil dicatat!";
            } elseif ($session->session_type === 'judge') {
                $notes = 'Presensi Dewan Juri';
                $customMessage = "Presensi Dewan Juri {$resolved['name']} berhasil dicatat!";
            }
        } elseif ($entityType === 'chaperone') {
            $notes = 'Presensi Pembina Pendamping: '.($entity->scoutGroup?->school_name ?? '-');
            $customMessage = "Presensi Pembina Pendamping {$resolved['name']} berhasil dicatat!";
        }

        // 6. Simpan catatan presensi baru
        $attendance = Attendance::create([
            'attendance_session_id' => $session->id,
            'entity_type' => $entityType,
            'participant_id' => $entityType === 'participant' ? $entity->id : null,
            'patrol_id' => $entityType === 'participant' ? $patrol?->id : null,
            'chaperone_id' => $entityType === 'chaperone' ? $entity->id : null,
            'user_id' => $entityType === 'user' ? $entity->id : null,
            'scanned_by_user_id' => $scannedBy?->id,
            'scanned_at' => now(),
            'status' => 'present',
            'notes' => $notes,
            'device_info' => $deviceInfo ?: request()->header('User-Agent'),
        ]);

        return [
            'success' => true,
            'code' => 'RECORDED',
            'message' => $customMessage ?: ('Presensi '.$resolved['name'].' ('.($resolved['id_number'] ?: 'ID-OK').') berhasil dicatat!'),
            'attendance' => $attendance,
            'attendee' => $this->formatAttendeePayload($attendance, $resolved),
        ];
    }

    /**
     * Presensi manual berdasarkan ID entitas dan tipe entitas
     */
    public function manualCheckIn(
        AttendanceSession $session,
        string $entityType,
        string $entityId,
        ?User $scannedBy = null,
        ?string $notes = null
    ): array {
        if (! $session->is_active) {
            return [
                'success' => false,
                'code' => 'SESSION_INACTIVE',
                'message' => 'Sesi presensi "'.$session->title.'" sedang ditutup atau belum diaktifkan.',
            ];
        }

        // Validasi kesesuaian jadwal buka dan tutup lomba
        $session->loadMissing('schedule');
        if (! $session->isScheduleOpen()) {
            $openTime = $session->getEffectiveOpenTime();
            $formattedTime = $openTime ? $openTime->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y [pukul] HH:mm') : '';

            return [
                'success' => false,
                'code' => 'SCHEDULE_NOT_OPEN',
                'message' => "Sesi presensi belum dibuka. Presensi dibuka paling cepat 30 menit sebelum waktu lomba dimulai ({$formattedTime} WIB).",
            ];
        }

        if ($session->isScheduleClosed()) {
            $closeTime = $session->getEffectiveCloseTime();
            $formattedTime = $closeTime ? $closeTime->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y [pukul] HH:mm') : '';

            return [
                'success' => false,
                'code' => 'SCHEDULE_CLOSED',
                'message' => "Sesi presensi telah ditutup. Jadwal sesi lomba ini telah berakhir pada {$formattedTime} WIB.",
            ];
        }

        // Cari entitas
        $entity = match ($entityType) {
            'participant' => Participant::with('patrol.scoutGroup')->find($entityId),
            'chaperone' => Chaperone::with('scoutGroup')->find($entityId),
            'user' => User::find($entityId),
            default => null,
        };

        if (! $entity) {
            return [
                'success' => false,
                'code' => 'ENTITY_NOT_FOUND',
                'message' => 'Data entitas tidak ditemukan.',
            ];
        }

        $session->loadMissing(['schedule.competition']);
        $schedule = $session->schedule;
        $competition = $schedule?->competition;
        $patrol = null;
        $assignment = null;

        // Validasi aturan entitas untuk Sesi Lomba vs Sesi Kehadiran Umum
        if ($session->isCompetition()) {
            if ($entityType === 'participant') {
                /** @var Participant $participant */
                $participant = $entity;
                $patrol = $participant->patrol;

                if (! $patrol) {
                    return [
                        'success' => false,
                        'code' => 'NO_PATROL',
                        'message' => "Peserta {$participant->full_name} belum terdaftar dalam regu manapun.",
                    ];
                }

                // A. Validasi Kategori Golongan (Putra vs Putri)
                if ($competition) {
                    $effectiveGender = $schedule?->effectiveGenderCategory();
                    if ($effectiveGender === 'boys' && $patrol->isGirls()) {
                        return [
                            'success' => false,
                            'code' => 'CATEGORY_MISMATCH',
                            'message' => "Sesi jadwal lomba ini diperuntukkan khusus bagi Regu Putra. {$participant->full_name} berasal dari Regu Putri ({$patrol->patrol_name}).",
                        ];
                    }
                    if ($effectiveGender === 'girls' && $patrol->isBoys()) {
                        return [
                            'success' => false,
                            'code' => 'CATEGORY_MISMATCH',
                            'message' => "Sesi jadwal lomba ini diperuntukkan khusus bagi Regu Putri. {$participant->full_name} berasal dari Regu Putra ({$patrol->patrol_name}).",
                        ];
                    }
                }

                // Cek duplikasi entitas peserta perorangan
                $existing = Attendance::where('attendance_session_id', $session->id)
                    ->where('participant_id', $entityId)
                    ->first();

                if ($existing) {
                    return [
                        'success' => false,
                        'code' => 'ALREADY_SCANNED',
                        'message' => 'Peserta '.$participant->full_name.' sudah tercatat hadir pada pukul '.$existing->scanned_at->format('H:i:s').' WIB.',
                    ];
                }

                // 1) Lomba Kategori Regu Utuh: Wajib PINRU / WAPINRU
                if ($schedule?->isPatrol()) {
                    $isLeader = $participant->isLeader() || $participant->isAssistantLeader();
                    if (! $isLeader) {
                        return [
                            'success' => false,
                            'code' => 'LEADER_REQUIRED',
                            'message' => "Presensi lomba kategori Regu Utuh wajib dilakukan oleh Pemimpin Regu (PINRU) atau Wakil Pemimpin Regu (WAPINRU). {$participant->full_name} terdaftar sebagai Anggota Regu.",
                            'attendee' => $this->formatAttendeePayload(null, $resolved),
                        ];
                    }

                    $existingPatrol = Attendance::where('attendance_session_id', $session->id)
                        ->where('patrol_id', $patrol->id)
                        ->first();

                    if ($existingPatrol) {
                        return [
                            'success' => false,
                            'code' => 'ALREADY_SCANNED',
                            'message' => "Regu Utuh {$patrol->patrol_name} sudah tercatat hadir pada pukul ".$existingPatrol->scanned_at->format('H:i:s')." WIB oleh {$existingPatrol->entity_name}.",
                        ];
                    }
                }

                // 2) Lomba Kategori Individu: Cukup salah satu perwakilan regu (1 orang)
                elseif ($schedule?->isIndividual()) {
                    $existingPatrol = Attendance::where('attendance_session_id', $session->id)
                        ->where('patrol_id', $patrol->id)
                        ->first();

                    if ($existingPatrol) {
                        return [
                            'success' => false,
                            'code' => 'ALREADY_SCANNED',
                            'message' => "Perwakilan Regu {$patrol->patrol_name} sudah tercatat hadir atas nama {$existingPatrol->entity_name} pada pukul ".$existingPatrol->scanned_at->format('H:i:s').' WIB.',
                        ];
                    }
                }

                // 3) Lomba Kategori Kelompok: Dibatasi kuota n orang per regu
                elseif ($schedule?->isGroup()) {
                    $maxParticipants = $competition?->max_participants ?: ($competition?->min_participants ?: 8);
                    $existingGroupCount = Attendance::where('attendance_session_id', $session->id)
                        ->where('patrol_id', $patrol->id)
                        ->count();

                    if ($existingGroupCount >= $maxParticipants) {
                        return [
                            'success' => false,
                            'code' => 'GROUP_QUOTA_EXCEEDED',
                            'message' => "Kuota presensi kelompok untuk Regu {$patrol->patrol_name} sudah terpenuhi ({$existingGroupCount}/{$maxParticipants} peserta). Seluruh anggota kelompok yang ditugaskan sudah hadir.",
                        ];
                    }
                }
            } elseif ($entityType === 'user') {
                /** @var User $userEntity */
                $userEntity = $entity;

                $assignment = $schedule ? $schedule->judgeAssignments()->where('user_id', $userEntity->id)->first() : null;

                if (! $assignment) {
                    if ($userEntity->isJudge()) {
                        return [
                            'success' => false,
                            'code' => 'JUDGE_NOT_ASSIGNED',
                            'message' => "Dewan Juri {$userEntity->name} tidak ditugaskan pada jadwal lomba ini.",
                        ];
                    }

                    if ($userEntity->isCommittee()) {
                        return [
                            'success' => false,
                            'code' => 'COMMITTEE_NOT_ELIGIBLE',
                            'message' => 'Panitia pelaksana tidak memerlukan absensi lomba.',
                        ];
                    }

                    return [
                        'success' => false,
                        'code' => 'USER_NOT_ELIGIBLE',
                        'message' => "Pengguna {$userEntity->name} tidak terdaftar sebagai peserta atau dewan juri pada lomba ini.",
                    ];
                }

                $existing = Attendance::where('attendance_session_id', $session->id)
                    ->where('user_id', $userEntity->id)
                    ->first();

                if ($existing) {
                    return [
                        'success' => false,
                        'code' => 'ALREADY_SCANNED',
                        'message' => "Dewan Juri {$userEntity->name} sudah tercatat hadir pada pukul ".$existing->scanned_at->format('H:i:s').' WIB.',
                    ];
                }
            } elseif ($entityType === 'chaperone') {
                return [
                    'success' => false,
                    'code' => 'CHAPERONE_NOT_ELIGIBLE',
                    'message' => 'Pembina Pendamping tidak memerlukan absensi lomba.',
                ];
            }
        } else {
            // Sesi Kehadiran Umum (Bukan Lomba): Validasi kesesuaian session_type
            if ($session->session_type === 'committee') {
                if ($entityType !== 'user' || ! $entity->isCommittee()) {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Panitia Pelaksana.',
                    ];
                }
            } elseif ($session->session_type === 'judge') {
                if ($entityType !== 'user' || ! $entity->isJudge()) {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Dewan Juri.',
                    ];
                }
            } elseif ($session->session_type === 'chaperone') {
                if ($entityType !== 'chaperone') {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Pembina Pendamping.',
                    ];
                }
            } elseif ($session->session_type === 'participant') {
                if ($entityType !== 'participant') {
                    return [
                        'success' => false,
                        'code' => 'SESSION_MISMATCH',
                        'message' => 'Sesi presensi ini khusus diperuntukkan bagi Peserta Lomba.',
                    ];
                }
            }

            // Cek duplikasi entitas perorangan pada sesi umum
            $existing = Attendance::where('attendance_session_id', $session->id)
                ->where(function ($query) use ($entityType, $entityId) {
                    if ($entityType === 'participant') {
                        $query->where('participant_id', $entityId);
                    } elseif ($entityType === 'chaperone') {
                        $query->where('chaperone_id', $entityId);
                    } else {
                        $query->where('user_id', $entityId);
                    }
                })
                ->first();

            if ($existing) {
                return [
                    'success' => false,
                    'code' => 'ALREADY_SCANNED',
                    'message' => 'Entitas ini sudah tercatat hadir pada pukul '.$existing->scanned_at->format('H:i:s').' WIB.',
                ];
            }
        }

        $defaultNotes = $notes;
        if (! $defaultNotes) {
            if ($entityType === 'participant') {
                if ($schedule?->isPatrol()) {
                    $defaultNotes = 'Presensi Regu Utuh Manual ('.($entity->isLeader() ? 'PINRU' : 'WAPINRU').')';
                } elseif ($schedule?->isGroup()) {
                    $maxParticipants = $competition?->max_participants ?: ($competition?->min_participants ?: 8);
                    $newCount = Attendance::where('attendance_session_id', $session->id)->where('patrol_id', $patrol?->id)->count() + 1;
                    $defaultNotes = "Peserta Kelompok ({$newCount}/{$maxParticipants}) • Regu ".($patrol?->patrol_name ?? '-');
                } elseif ($schedule?->isIndividual()) {
                    $defaultNotes = 'Presensi Manual Perwakilan Regu: '.($patrol?->patrol_name ?? '-');
                } else {
                    $defaultNotes = 'Input Manual Petugas Pos';
                }
            } elseif ($entityType === 'user') {
                if ($session->isCompetition()) {
                    $roleText = $assignment?->roleLabel() ?: 'Dewan Juri';
                    $defaultNotes = 'Dewan Juri: '.$roleText;
                } elseif ($session->session_type === 'committee') {
                    $defaultNotes = 'Presensi Panitia: '.($entity->committee_position ?: $entity->position ?: 'Panitia Pelaksana');
                } elseif ($session->session_type === 'judge') {
                    $defaultNotes = 'Presensi Dewan Juri';
                }
            } elseif ($entityType === 'chaperone') {
                $defaultNotes = 'Presensi Pembina Pendamping: '.($entity->scoutGroup?->school_name ?? '-');
            }
        }

        $attendance = Attendance::create([
            'attendance_session_id' => $session->id,
            'entity_type' => $entityType,
            'participant_id' => $entityType === 'participant' ? $entityId : null,
            'patrol_id' => $entityType === 'participant' ? $patrol?->id : null,
            'chaperone_id' => $entityType === 'chaperone' ? $entityId : null,
            'user_id' => $entityType === 'user' ? $entityId : null,
            'scanned_by_user_id' => $scannedBy?->id,
            'scanned_at' => now(),
            'status' => 'present',
            'notes' => $defaultNotes ?: 'Input Manual Petugas Pos',
            'device_info' => 'Manual Web Portal',
        ]);

        return [
            'success' => true,
            'code' => 'RECORDED',
            'message' => 'Presensi manual berhasil dicatat!',
            'attendance' => $attendance,
        ];
    }

    /**
     * Pencarian kandidat untuk input presensi manual (dengan flag apakah sudah hadir)
     */
    public function searchCandidates(AttendanceSession $session, string $keyword): Collection
    {
        $term = trim($keyword);
        if (empty($term)) {
            return collect();
        }

        $session->loadMissing(['schedule.competition']);
        $schedule = $session->schedule;
        $competition = $schedule?->competition;
        $isPatrolSession = $schedule?->isPatrol();
        $isGroupSession = $schedule?->isGroup();
        $isIndividualSession = $schedule?->isIndividual();

        // Ambil ID peserta dan ID regu yang sudah hadir di sesi ini
        $attendedParticipantIds = Attendance::where('attendance_session_id', $session->id)
            ->whereNotNull('participant_id')
            ->pluck('participant_id')
            ->toArray();

        $attendedPatrolCounts = Attendance::where('attendance_session_id', $session->id)
            ->whereNotNull('patrol_id')
            ->selectRaw('patrol_id, count(*) as total')
            ->groupBy('patrol_id')
            ->pluck('total', 'patrol_id')
            ->toArray();

        $maxGroupParticipants = $competition?->max_participants ?: 8;

        $participants = Participant::with(['patrol.scoutGroup'])
            ->where(function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('id_number', 'like', "%{$term}%")
                    ->orWhere('scout_id', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($p) use ($schedule, $attendedParticipantIds, $attendedPatrolCounts, $isPatrolSession, $isGroupSession, $isIndividualSession, $maxGroupParticipants) {
                $isLeader = $p->isLeader() || $p->isAssistantLeader();
                $isParticipantAttended = in_array($p->id, $attendedParticipantIds);
                $patrolAttendanceCount = $p->patrol_id ? ($attendedPatrolCounts[$p->patrol_id] ?? 0) : 0;
                $isPatrolAttended = $patrolAttendanceCount > 0;

                $isEligible = true;
                $eligibilityNote = null;
                $isAttended = false;

                // Validasi kesesuaian kategori gender regu dengan jadwal lomba
                $effectiveGender = $schedule?->effectiveGenderCategory() ?? 'all';
                if ($effectiveGender === 'boys' && $p->patrol?->isGirls()) {
                    $isEligible = false;
                    $eligibilityNote = 'Regu Putri (jadwal Putra)';
                } elseif ($effectiveGender === 'girls' && $p->patrol?->isBoys()) {
                    $isEligible = false;
                    $eligibilityNote = 'Regu Putra (jadwal Putri)';
                }

                if ($isEligible && $isPatrolSession) {
                    $isAttended = $isParticipantAttended || $isPatrolAttended;
                    if (! $isLeader) {
                        $isEligible = false;
                        $eligibilityNote = 'Bukan PINRU/WAPINRU';
                    } elseif ($isPatrolAttended) {
                        $isEligible = false;
                        $eligibilityNote = 'Regu Utuh sudah presensi';
                    }
                } elseif ($isGroupSession) {
                    $isAttended = $isParticipantAttended;
                    if ($isParticipantAttended) {
                        $isEligible = false;
                        $eligibilityNote = 'Peserta sudah presensi';
                    } elseif ($patrolAttendanceCount >= $maxGroupParticipants) {
                        $isEligible = false;
                        $eligibilityNote = "Kuota kelompok penuh ({$patrolAttendanceCount}/{$maxGroupParticipants})";
                    }
                } elseif ($isIndividualSession) {
                    $isAttended = $isParticipantAttended;
                    if ($isParticipantAttended) {
                        $isEligible = false;
                        $eligibilityNote = 'Peserta sudah presensi';
                    } elseif ($isPatrolAttended) {
                        $isEligible = false;
                        $eligibilityNote = 'Regu sudah diwakili';
                    }
                } else {
                    $isAttended = $isParticipantAttended;
                }

                return [
                    'id' => $p->id,
                    'type' => 'participant',
                    'name' => $p->full_name,
                    'id_number' => $p->id_number ?: 'ID-PESERTA',
                    'sub_title' => ($p->patrol?->scoutGroup?->school_name ?? '-').' • Regu '.($p->patrol?->patrol_name ?? '-'),
                    'role_label' => match ($p->role_in_patrol?->value) {
                        'patrol_leader' => 'PINRU',
                        'assistant_patrol_leader' => 'WAPINRU',
                        default => 'Anggota',
                    },
                    'photo_url' => $p->photo_path ? asset('storage/'.$p->photo_path) : null,
                    'is_attended' => $isAttended,
                    'is_eligible' => $isEligible,
                    'eligibility_note' => $eligibilityNote,
                ];
            });

        // Pada Sesi Lomba: Hanya sertakan Peserta Lomba dan Dewan Juri yang ditugaskan (tanpa panitia atau bindamping)
        if ($session->isCompetition()) {
            $assignedJudges = $schedule ? $schedule->judges()
                ->where(function ($q) use ($term) {
                    $q->where('users.name', 'like', "%{$term}%")
                        ->orWhere('users.id_number', 'like', "%{$term}%")
                        ->orWhere('users.username', 'like', "%{$term}%");
                })
                ->limit(5)
                ->get()
                ->map(function ($u) use ($session) {
                    $att = Attendance::where('attendance_session_id', $session->id)
                        ->where('user_id', $u->id)
                        ->first();
                    $isAttended = $att !== null;

                    return [
                        'id' => $u->id,
                        'type' => 'user',
                        'name' => $u->name,
                        'id_number' => $u->id_number ?: 'ID-JURI',
                        'sub_title' => ($u->institution ?: 'Kwartir Cabang').' • Dewan Juri Ditugaskan',
                        'role_label' => 'Dewan Juri',
                        'photo_url' => $u->avatar_path ? asset('storage/'.$u->avatar_path) : null,
                        'is_attended' => $isAttended,
                        'is_eligible' => ! $isAttended,
                        'eligibility_note' => $isAttended ? 'Sudah presensi ('.$att->scanned_at->format('H:i').' WIB)' : null,
                    ];
                }) : collect();

            return $participants->concat($assignedJudges);
        }

        // Sesi Kehadiran Umum Khusus Panitia atau Dewan Juri
        if ($session->session_type === 'committee' || $session->session_type === 'judge') {
            $attendedUserIds = Attendance::where('attendance_session_id', $session->id)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();

            $targetRole = $session->session_type === 'committee' ? RoleEnum::Committee : RoleEnum::Judge;

            return User::role($targetRole)
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('id_number', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($u) use ($attendedUserIds) {
                    $isAttended = in_array($u->id, $attendedUserIds);

                    return [
                        'id' => $u->id,
                        'type' => 'user',
                        'name' => $u->name,
                        'id_number' => $u->id_number ?: 'ID-APARATUR',
                        'sub_title' => ($u->institution ?: 'Kwartir Cabang').' • '.($u->position ?: $u->roleLabel()),
                        'role_label' => $u->roleLabel(),
                        'photo_url' => $u->avatar_path ? asset('storage/'.$u->avatar_path) : null,
                        'is_attended' => $isAttended,
                        'is_eligible' => ! $isAttended,
                        'eligibility_note' => $isAttended ? 'Sudah presensi' : null,
                    ];
                });
        }

        // Sesi Kehadiran Umum Khusus Pembina Pendamping
        if ($session->session_type === 'chaperone') {
            $attendedChaperoneIds = Attendance::where('attendance_session_id', $session->id)
                ->whereNotNull('chaperone_id')
                ->pluck('chaperone_id')
                ->toArray();

            return Chaperone::with('scoutGroup')
                ->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', "%{$term}%")
                        ->orWhere('id_number', 'like', "%{$term}%")
                        ->orWhere('scout_id', 'like', "%{$term}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($c) use ($attendedChaperoneIds) {
                    $isAttended = in_array($c->id, $attendedChaperoneIds);

                    return [
                        'id' => $c->id,
                        'type' => 'chaperone',
                        'name' => $c->full_name,
                        'id_number' => $c->id_number ?: 'ID-PENDAMPING',
                        'sub_title' => ($c->scoutGroup?->school_name ?? '-').' • Pembina '.($c->gender === 'male' ? 'Putra' : 'Putri'),
                        'role_label' => 'Pembina Pendamping',
                        'photo_url' => $c->photo_path ? asset('storage/'.$c->photo_path) : null,
                        'is_attended' => $isAttended,
                        'is_eligible' => ! $isAttended,
                        'eligibility_note' => $isAttended ? 'Sudah presensi' : null,
                    ];
                });
        }

        return $participants;
    }

    /**
     * Format payload respons data entitas yang hadir
     */
    protected function formatAttendeePayload(?Attendance $attendance, array $resolved): array
    {
        return [
            'id' => $attendance?->id,
            'name' => $resolved['name'],
            'id_number' => $resolved['id_number'] ?: '-',
            'role_label' => $resolved['role_label'],
            'category_label' => $resolved['category_label'] ?? '-',
            'school_name' => $resolved['school_name'] ?? '-',
            'patrol_name' => $resolved['patrol_name'] ?? '-',
            'group_number' => $resolved['group_number'] ?? '-',
            'photo_url' => ! empty($resolved['photo_path']) ? asset('storage/'.$resolved['photo_path']) : null,
            'scanned_at_formatted' => $attendance ? ($attendance->scanned_at->format('H:i:s').' WIB') : (now()->format('H:i:s').' WIB'),
            'status' => $attendance?->status ?? 'invalid',
        ];
    }
}
