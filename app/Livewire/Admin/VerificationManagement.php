<?php

namespace App\Livewire\Admin;

use App\Enums\RegistrationStatusEnum;
use App\Models\Participant;
use App\Models\ScoutGroup;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Verifikasi Berkas & Pendaftaran Kontingen Gudep
 */
#[Layout('layouts.app')]
#[Title('Verifikasi Pendaftaran Kontingen — Livewire 4')]
class VerificationManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    // State Modal Quick Review & Verification
    public bool $showReviewModal = false;
    public ?string $selectedGroupId = null;
    public string $actionType = 'verify'; // 'verify', 'revision', 'reject'
    public string $revisionNotes = '';
    public string $rejectReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'all' => ScoutGroup::count(),
            'submitted' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Submitted)->count(),
            'verified' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count(),
            'needs_revision' => ScoutGroup::where('verification_status', RegistrationStatusEnum::NeedsRevision)->count(),
            'rejected' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Rejected)->count(),
        ];
    }

    #[Computed]
    public function selectedGroup(): ?ScoutGroup
    {
        if (! $this->selectedGroupId) {
            return null;
        }

        return ScoutGroup::with(['user', 'patrols.participants', 'chaperones', 'verifier'])
            ->find($this->selectedGroupId);
    }

    #[Computed]
    public function scoutGroups()
    {
        $query = ScoutGroup::with(['user', 'patrols.participants', 'chaperones', 'verifier']);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('school_name', 'like', '%' . $this->search . '%')
                    ->orWhere('group_number', 'like', '%' . $this->search . '%')
                    ->orWhere('registration_code', 'like', '%' . $this->search . '%')
                    ->orWhere('kwarran_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all' && ! empty($this->statusFilter)) {
            $query->where('verification_status', $this->statusFilter);
        }

        return $query->latest()->paginate(10);
    }

    /**
     * Buka modal quick inspection berkas pendaftaran
     */
    public function openReviewModal(string $groupId, string $action = 'view'): void
    {
        $this->selectedGroupId = $groupId;
        $this->actionType = $action;
        $this->revisionNotes = '';
        $this->rejectReason = '';
        $this->showReviewModal = true;
    }

    /**
     * Setujui pendaftaran kontingen secara instan
     */
    public function verifyContingent(string $groupId): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang melakukan verifikasi pendaftaran kontingen.');
        }

        $group = ScoutGroup::findOrFail($groupId);

        $group->update([
            'verification_status' => RegistrationStatusEnum::Verified,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'revision_notes' => null,
        ]);

        $group->patrols()->update(['status' => RegistrationStatusEnum::Verified]);
        Participant::whereIn('patrol_id', $group->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Verified]);
        $group->chaperones()->update(['status' => RegistrationStatusEnum::Verified]);

        Flux::toast(variant: 'success', heading: 'Kontingen Disetujui', text: "Pendaftaran {$group->school_name} berhasil diverifikasi.");
        $this->showReviewModal = false;
    }

    /**
     * Minta perbaikan data kontingen
     */
    public function requestRevision(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang meminta revisi berkas pendaftaran.');
        }

        $this->validate([
            'revisionNotes' => 'required|string|min:5',
        ]);

        if (! $this->selectedGroupId) {
            return;
        }

        $group = ScoutGroup::findOrFail($this->selectedGroupId);

        $group->update([
            'verification_status' => RegistrationStatusEnum::NeedsRevision,
            'revision_notes' => $this->revisionNotes,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $group->patrols()->update(['status' => RegistrationStatusEnum::NeedsRevision]);
        Participant::whereIn('patrol_id', $group->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::NeedsRevision]);
        $group->chaperones()->update(['status' => RegistrationStatusEnum::NeedsRevision]);

        Flux::toast(variant: 'warning', heading: 'Permintaan Revisi Terkirim', text: "Catatan perbaikan telah dikirim ke Gudep {$group->school_name}.");
        $this->showReviewModal = false;
    }

    /**
     * Tolak pendaftaran kontingen
     */
    public function rejectContingent(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang menolak pendaftaran kontingen.');
        }

        $this->validate([
            'rejectReason' => 'required|string|min:5',
        ]);

        if (! $this->selectedGroupId) {
            return;
        }

        $group = ScoutGroup::findOrFail($this->selectedGroupId);

        $group->update([
            'verification_status' => RegistrationStatusEnum::Rejected,
            'revision_notes' => 'Alasan Penolakan: ' . $this->rejectReason,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $group->patrols()->update(['status' => RegistrationStatusEnum::Rejected]);
        Participant::whereIn('patrol_id', $group->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Rejected]);
        $group->chaperones()->update(['status' => RegistrationStatusEnum::Rejected]);

        Flux::toast(variant: 'danger', heading: 'Pendaftaran Ditolak', text: "Pendaftaran {$group->school_name} telah ditolak.");
        $this->showReviewModal = false;
    }

    public function render()
    {
        return view('livewire.admin.verification-management', [
            'scoutGroups' => $this->scoutGroups,
            'stats' => $this->stats,
            'selectedGroup' => $this->selectedGroup,
        ]);
    }
}
