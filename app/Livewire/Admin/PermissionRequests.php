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
    /** create = permohonan baru, return = konfirmasi kembali, history = riwayat */
    public string $tab = 'create';

    public string $type = 'leave_permanent';

    public string $reason = '';

    public string $guest_name = '';

    public string $manualInput = '';

    /** @var array<int, array{kind: string, id: string, name: string, role: string, scout_id: string, group_id: string, group_name: string}> */
    public array $members = [];

    /** @var array{type: string, message: string}|null */
    public ?array $feedback = null;

    /** URL surat izin yang baru dibuat (untuk tombol cetak) */
    public ?string $lastLetterUrl = null;

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['create', 'return', 'history'], true) ? $tab : 'create';
        $this->feedback = null;
        $this->lastLetterUrl = null;
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
            'guest_name' => ['nullable', 'string', 'max:255'],
        ], [
            'reason.required' => 'Keterangan (alasan) wajib diisi.',
            'reason.min' => 'Keterangan minimal 5 karakter.',
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
     * Konfirmasi kembali izin sementara: hanya peserta
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
            ->with('permissionRequest')
            ->first();

        if (! $member) {
            $this->fail($resolved['name'].' tidak memiliki izin sementara yang menunggu konfirmasi kembali.');

            return;
        }

        $member->update(['returned_at' => now()]);
        $member->permissionRequest->completeIfAllReturned();

        $this->feedback = ['type' => 'success', 'message' => $resolved['name'].' dikonfirmasi telah kembali ke perkemahan.'];
        $this->dispatch('permission-updated');
    }

    private function fail(string $message): void
    {
        $this->feedback = ['type' => 'error', 'message' => $message];
    }

    public function render()
    {
        return view('livewire.admin.permission-requests', [
            'types' => PermissionTypeEnum::cases(),
            'selectedType' => $this->permissionType(),
        ]);
    }
}
