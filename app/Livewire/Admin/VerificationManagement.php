<?php

namespace App\Livewire\Admin;

use App\Enums\RegistrationStatusEnum;
use App\Models\ScoutGroup;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen antrean verifikasi berkas & pendaftaran kontingen Gugus Depan
 */
#[Layout('layouts.app')]
#[Title('Verifikasi Pendaftaran Kontingen — LT III')]
class VerificationManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

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

    public function render()
    {
        return view('livewire.admin.verification-management', [
            'stats' => $this->stats,
            'scoutGroups' => $this->scoutGroups,
        ]);
    }
}
