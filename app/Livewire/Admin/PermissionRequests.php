<?php

namespace App\Livewire\Admin;

use App\Enums\PermissionTypeEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\PermissionRequest;
use App\Models\PermissionRequestMember;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Permohonan izin (hanya Administrator): meninggalkan perkemahan permanen/sementara dan kunjungan tamu.
 * Izin meninggalkan perkemahan wajib memindai QR peserta dan bindamping dari gudep yang sama;
 * izin sementara dikonfirmasi kembali dengan memindai QR peserta.
 */
#[Layout('layouts.app', ['title' => 'Permohonan Izin'])]
#[Title('Permohonan Izin — LT III')]
class PermissionRequests extends Component
{
    /** create = permohonan baru, return = konfirmasi kembali, unreturned = daftar belum kembali, history = riwayat */
    #[Url]
    public string $tab = 'create';

    public string $type = 'leave_permanent';

    public string $reason = '';

    public string $guest_name = '';

    public string $manualInput = '';

    public string $unreturnedSearch = '';

    public string $unreturnedGender = 'all';

    /** @var array<int, array{kind: string, id: string, name: string, role: string, scout_id: string, group_id: string, group_name: string}> */
    public array $members = [];

    /** @var array{type: string, message: string}|null */
    public ?array $feedback = null;

    /** URL surat izin yang baru dibuat (untuk tombol cetak) */
    public ?string $lastLetterUrl = null;

    /** Status modal verifikasi 2 tahap konfirmasi kembali */
    public bool $showReturnModal = false;

    public ?string $returnMemberId = null;

    /** @var array<string, mixed>|null */
    public ?array $returnParticipantData = null;

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['create', 'return', 'unreturned', 'history'], true) ? $tab : 'create';
        $this->feedback = null;
        $this->lastLetterUrl = null;
        $this->closeReturnModal();
    }

    public function updatedType(): void
    {
        if (! $this->permissionType()->requiresScan()) {
            $this->members = [];
        }

        $this->feedback = null;
    }

    public function scanManual(): void
    {
        $this->handleScan($this->manualInput);
        $this->manualInput = '';
    }

    /**
     * Hasil pindai: menambah anggota pada permohonan baru, atau konfirmasi kembali pada tab "return"
     */
    public function handleScan(string $raw): void
    {
        $token = app(QrCodeService::class)->tokenFromScan($raw);
        $resolved = $token !== '' ? app(QrCodeService::class)->resolveToken($token) : null;

        if (! $resolved || ! in_array($resolved['type'], ['participant', 'chaperone'], true)) {
            $this->fail('QR tidak dikenali atau bukan QR peserta/bindamping.');

            return;
        }

        $this->tab === 'return' ? $this->confirmReturn($resolved) : $this->addMember($resolved);
    }

    public function removeMember(int $index): void
    {
        unset($this->members[$index]);
        $this->members = array_values($this->members);
    }

    /**
     * Simpan permohonan; izin permanen langsung selesai, izin sementara menunggu konfirmasi kembali
     */
    public function submit(): void
    {
        $type = $this->permissionType();

        $this->validate([
            'type' => ['required', Rule::enum(PermissionTypeEnum::class)],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'guest_name' => [
                $type === PermissionTypeEnum::GuestVisit ? 'required' : 'nullable',
                'string',
                'min:2',
                'max:255',
            ],
        ], [
            'reason.required' => 'Keterangan (alasan) wajib diisi.',
            'reason.min' => 'Keterangan minimal 5 karakter.',
            'guest_name.required' => 'Kunjungan tamu wajib menginputkan minimal 1 nama tamu.',
            'guest_name.min' => 'Nama tamu minimal 2 karakter.',
        ]);

        if ($type->requiresScan()) {
            $kinds = collect($this->members)->pluck('kind');

            if ($kinds->doesntContain('chaperone') || $kinds->doesntContain('participant')) {
                $this->addError('members', 'Wajib memindai QR minimal 1 bindamping dan 1 peserta dari gudep yang sama.');

                return;
            }
        }

        $request = DB::transaction(function () use ($type) {
            $request = PermissionRequest::create([
                'type' => $type,
                'reason' => trim($this->reason),
                'guest_name' => $type === PermissionTypeEnum::GuestVisit ? ($this->guest_name ?: null) : null,
                'scout_group_id' => $this->members[0]['group_id'] ?? null,
                'status' => $type->requiresReturn() ? 'active' : 'completed',
                'completed_at' => $type->requiresReturn() ? null : now(),
                'created_by' => auth()->user()->recordableId(),
            ]);

            foreach ($this->members as $member) {
                PermissionRequestMember::create([
                    'permission_request_id' => $request->id,
                    $member['kind'] === 'participant' ? 'participant_id' : 'chaperone_id' => $member['id'],
                ]);
            }

            return $request;
        });

        $this->reset(['reason', 'guest_name', 'members', 'manualInput']);
        $this->resetValidation();
        $this->feedback = ['type' => 'success', 'message' => $type->requiresReturn()
            ? 'Izin sementara dicatat. Peserta wajib dikonfirmasi saat kembali (tab Konfirmasi Kembali).'
            : 'Permohonan izin berhasil dicatat.'];
        $this->lastLetterUrl = route('admin.permission-requests.letter', $request->id);
        $this->dispatch('permission-updated');
        $this->dispatch('permission-letter-ready', url: $this->lastLetterUrl);
    }

    private function permissionType(): PermissionTypeEnum
    {
        return PermissionTypeEnum::tryFrom($this->type) ?? PermissionTypeEnum::LeavePermanent;
    }

    /**
     * @param  array<string, mixed>  $resolved  hasil QrCodeService::resolveToken
     */
    private function addMember(array $resolved): void
    {
        if (! $this->permissionType()->requiresScan()) {
            $this->fail('Jenis izin ini tidak memerlukan pemindaian QR.');

            return;
        }

        if (! $resolved['is_verified']) {
            $this->fail('Kontingen belum terverifikasi, QR tidak dapat dipakai.');

            return;
        }

        $entity = $resolved['entity'];
        $isParticipant = $resolved['type'] === 'participant';
        $group = $isParticipant ? $entity->patrol?->scoutGroup : $entity->scoutGroup;

        if (collect($this->members)->contains(fn ($m) => $m['id'] === $entity->id)) {
            $this->fail($resolved['name'].' sudah ada dalam daftar.');

            return;
        }

        if ($this->members && $this->members[0]['group_id'] !== $group?->id) {
            $this->fail('Seluruh peserta dan bindamping harus berasal dari gudep yang sama ('.$this->members[0]['group_name'].').');

            return;
        }

        if ($blocked = $this->activeLeaveMessage($entity, $isParticipant)) {
            $this->fail($blocked);

            return;
        }

        $this->members[] = [
            'kind' => $resolved['type'],
            'id' => $entity->id,
            'name' => $resolved['name'],
            'role' => $resolved['role_label'],
            'scout_id' => (string) ($resolved['scout_id'] ?? '-'),
            'group_id' => (string) $group?->id,
            'group_name' => (string) $group?->school_name,
        ];
        $this->resetErrorBag('members');
        $this->feedback = ['type' => 'success', 'message' => $resolved['name'].' ditambahkan ('.$resolved['role_label'].').'];
    }

    /**
     * Peserta/bindamping tidak boleh diberi izin ganda: sedang izin sementara atau sudah izin permanen
     */
    private function activeLeaveMessage(Participant|Chaperone $entity, bool $isParticipant): ?string
    {
        $column = $isParticipant ? 'participant_id' : 'chaperone_id';

        $existing = PermissionRequestMember::where($column, $entity->id)
            ->whereHas('permissionRequest', function ($q) {
                $q->where('type', PermissionTypeEnum::LeavePermanent->value)
                    ->orWhere(fn ($q2) => $q2->where('type', PermissionTypeEnum::LeaveTemporary->value)->where('status', 'active'));
            })
            ->with('permissionRequest')
            ->first();

        if (! $existing) {
            return null;
        }

        return $existing->permissionRequest->type === PermissionTypeEnum::LeavePermanent
            ? 'Sudah memiliki izin meninggalkan perkemahan secara permanen.'
            : 'Masih dalam izin sementara dan belum dikonfirmasi kembali.';
    }

    /**
     * Konfirmasi kembali izin sementara: hanya peserta (tahap 1 verifikasi QR)
     *
     * @param  array<string, mixed>  $resolved
     */
    private function confirmReturn(array $resolved): void
    {
        if ($resolved['type'] !== 'participant') {
            $this->fail('Konfirmasi kembali hanya untuk peserta; bindamping tidak perlu dikonfirmasi.');

            return;
        }

        $member = PermissionRequestMember::where('participant_id', $resolved['entity']->id)
            ->whereNull('returned_at')
            ->whereHas('permissionRequest', fn ($q) => $q->where('type', PermissionTypeEnum::LeaveTemporary->value)->where('status', 'active'))
            ->with([
                'participant.patrol.scoutGroup',
                'permissionRequest.scoutGroup',
                'permissionRequest.members.chaperone',
            ])
            ->first();

        if (! $member) {
            $this->fail($resolved['name'].' tidak memiliki izin sementara yang menunggu konfirmasi kembali.');

            return;
        }

        $this->prepareReturnConfirmation($member);
    }

    /**
     * Konfirmasi kembali secara manual dari daftar peserta belum kembali (tahap 1 verifikasi manual)
     */
    public function confirmReturnManual(string $memberId): void
    {
        $member = PermissionRequestMember::where('id', $memberId)
            ->whereNotNull('participant_id')
            ->whereNull('returned_at')
            ->whereHas('permissionRequest', fn ($q) => $q->where('type', PermissionTypeEnum::LeaveTemporary->value)->where('status', 'active'))
            ->with([
                'participant.patrol.scoutGroup',
                'permissionRequest.scoutGroup',
                'permissionRequest.members.chaperone',
            ])
            ->first();

        if (! $member) {
            $this->fail('Data peserta tidak ditemukan atau sudah dikonfirmasi kembali.');

            return;
        }

        $this->prepareReturnConfirmation($member);
    }

    /**
     * Buka modal verifikasi 2 tahap dengan ringkasan identitas peserta dan izinnya
     */
    public function prepareReturnConfirmation(PermissionRequestMember $member): void
    {
        $participant = $member->participant;
        $request = $member->permissionRequest;
        $chaperone = $request?->members->firstWhere('chaperone_id', '!=', null)?->chaperone;

        $this->returnMemberId = (string) $member->id;
        $this->returnParticipantData = [
            'id' => $member->id,
            'name' => $participant?->full_name ?? '-',
            'id_number' => $participant?->id_number ?? '-',
            'scout_id' => $participant?->scout_id ?? '-',
            'gender' => $participant?->gender?->value ?? ($participant?->gender ?? '-'),
            'is_male' => $participant?->isMale() ?? false,
            'is_female' => $participant?->isFemale() ?? false,
            'photo_path' => $participant?->photo_path,
            'patrol_name' => $participant?->patrol?->patrol_name ?? '-',
            'school_name' => $participant?->patrol?->scoutGroup?->school_name ?? $request?->scoutGroup?->school_name ?? '-',
            'kwarran_name' => $participant?->patrol?->scoutGroup?->kwarran_name ?? $request?->scoutGroup?->kwarran_name ?? '-',
            'letter_number' => $request?->letterNumber() ?? '-',
            'reason' => $request?->reason ?? '-',
            'destination' => $request?->destination ?? '-',
            'departure_time' => $request?->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB',
            'departure_diff' => $request?->created_at?->diffForHumans(),
            'chaperone_name' => $chaperone?->full_name ?? '-',
            'chaperone_phone' => $chaperone?->phone_number ?? '-',
        ];
        $this->showReturnModal = true;
    }

    /**
     * Tutup modal verifikasi 2 tahap
     */
    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->returnMemberId = null;
        $this->returnParticipantData = null;
    }

    /**
     * Tahap 2: Eksekusi konfirmasi kembali setelah diverifikasi fisik oleh panitia
     */
    public function processReturnConfirmation(): void
    {
        if (! $this->returnMemberId) {
            $this->closeReturnModal();

            return;
        }

        $member = PermissionRequestMember::where('id', $this->returnMemberId)
            ->whereNotNull('participant_id')
            ->whereNull('returned_at')
            ->whereHas('permissionRequest', fn ($q) => $q->where('type', PermissionTypeEnum::LeaveTemporary->value)->where('status', 'active'))
            ->with(['participant', 'permissionRequest'])
            ->first();

        if (! $member) {
            $this->fail('Data peserta tidak valid atau sudah dikonfirmasi kembali.');
            $this->closeReturnModal();

            return;
        }

        $name = $member->participant?->full_name ?? 'Peserta';
        $member->update(['returned_at' => now()]);
        $member->permissionRequest->completeIfAllReturned();

        $this->feedback = [
            'type' => 'success',
            'message' => "Verifikasi 2 tahap berhasil. Peserta {$name} telah resmi dikonfirmasi kembali ke perkemahan.",
        ];
        $this->closeReturnModal();
        $this->dispatch('permission-updated');
    }

    private function fail(string $message): void
    {
        $this->feedback = ['type' => 'error', 'message' => $message];
    }

    public function render()
    {
        $baseUnreturnedQuery = PermissionRequestMember::whereNotNull('participant_id')
            ->whereNull('returned_at')
            ->whereHas('permissionRequest', fn ($q) => $q->where('type', PermissionTypeEnum::LeaveTemporary->value)->where('status', 'active'));

        $unreturnedCount = (clone $baseUnreturnedQuery)->count();

        $unreturnedMembers = (clone $baseUnreturnedQuery)
            ->with([
                'participant.patrol.scoutGroup',
                'permissionRequest.scoutGroup',
                'permissionRequest.members.chaperone',
            ])
            ->when($this->unreturnedSearch !== '', function ($q) {
                $term = '%'.trim($this->unreturnedSearch).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('participant', function ($pq) use ($term) {
                        $pq->where('full_name', 'like', $term)
                            ->orWhere('id_number', 'like', $term)
                            ->orWhere('scout_id', 'like', $term)
                            ->orWhereHas('patrol', function ($ptq) use ($term) {
                                $ptq->where('patrol_name', 'like', $term)
                                    ->orWhereHas('scoutGroup', function ($sgq) use ($term) {
                                        $sgq->where('school_name', 'like', $term);
                                    });
                            });
                    })->orWhereHas('permissionRequest', function ($prq) use ($term) {
                        $prq->where('letter_number', 'like', $term)
                            ->orWhere('reason', 'like', $term);
                    });
                });
            })
            ->when($this->unreturnedGender !== 'all', function ($q) {
                $q->whereHas('participant', fn ($pq) => $pq->where('gender', $this->unreturnedGender));
            })
            ->latest()
            ->get();

        $unreturnedRequestsCount = PermissionRequest::where('type', PermissionTypeEnum::LeaveTemporary)
            ->where('status', 'active')
            ->count();

        $unreturnedGroupsCount = (clone $baseUnreturnedQuery)
            ->join('participants', 'permission_request_members.participant_id', '=', 'participants.id')
            ->join('patrols', 'participants.patrol_id', '=', 'patrols.id')
            ->distinct('patrols.scout_group_id')
            ->count('patrols.scout_group_id');

        return view('livewire.admin.permission-requests', [
            'types' => PermissionTypeEnum::cases(),
            'selectedType' => $this->permissionType(),
            'unreturnedCount' => $unreturnedCount,
            'unreturnedMembers' => $unreturnedMembers,
            'unreturnedRequestsCount' => $unreturnedRequestsCount,
            'unreturnedGroupsCount' => $unreturnedGroupsCount,
        ]);
    }
}
