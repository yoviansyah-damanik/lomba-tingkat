<?php

namespace App\Livewire\Admin;

use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Participant;
use App\Models\ScoutGroup;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Direktori & Manajemen Data Seluruh Peserta Terverifikasi untuk Administrator
 */
#[Layout('layouts.app')]
#[Title('Direktori Peserta Terverifikasi — Administrator LT III')]
class ParticipantsDirectory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $genderFilter = 'all'; // 'all', 'male', 'female'
    public string $roleFilter = 'all';   // 'all', 'patrol_leader', 'assistant_patrol_leader', 'member'
    public string $scoutGroupFilter = 'all'; // 'all' or scout_group_id
    public string $statusFilter = 'verified'; // 'verified' (default per requirement), or 'all'
    public int $perPage = 15;

    // State Modal Slide-Over Biodata Detail Peserta
    public bool $showDetailModal = false;
    public ?string $selectedParticipantId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'genderFilter' => ['except' => 'all'],
        'roleFilter' => ['except' => 'all'],
        'scoutGroupFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'verified'],
        'perPage' => ['except' => 15],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGenderFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedScoutGroupFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Membuka modal detail biodata peserta
     */
    public function openDetail(string $participantId): void
    {
        $this->selectedParticipantId = $participantId;
        $this->showDetailModal = true;
    }

    /**
     * Menutup modal detail biodata peserta
     */
    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedParticipantId = null;
    }

    /**
     * Mereset seluruh filter ke nilai default
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'genderFilter', 'roleFilter', 'scoutGroupFilter', 'statusFilter']);
        $this->statusFilter = 'verified';
        $this->resetPage();
    }

    /**
     * Mengambil data statistik peserta terverifikasi
     */
    #[Computed]
    public function stats(): array
    {
        $baseQuery = Participant::query()
            ->where(function ($q) {
                $q->where('participants.status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('patrol.scoutGroup', function ($sg) {
                        $sg->where('verification_status', RegistrationStatusEnum::Verified);
                    });
            });

        $totalVerified = (clone $baseQuery)->count();

        $boysVerified = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('participants.gender', 'male')
                    ->orWhereHas('patrol', fn ($pq) => $pq->where('gender_category', 'boys'));
            })
            ->count();

        $girlsVerified = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('participants.gender', 'female')
                    ->orWhereHas('patrol', fn ($pq) => $pq->where('gender_category', 'girls'));
            })
            ->count();

        $leadersVerified = (clone $baseQuery)
            ->whereIn('participants.role_in_patrol', [
                PatrolRoleEnum::PatrolLeader->value,
                PatrolRoleEnum::AssistantPatrolLeader->value,
            ])
            ->count();

        $totalSchools = ScoutGroup::query()
            ->where(function ($sg) {
                $sg->where('verification_status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('patrols.participants', fn ($pq) => $pq->where('status', RegistrationStatusEnum::Verified));
            })
            ->count();

        return [
            'total_verified' => $totalVerified,
            'boys_verified' => $boysVerified,
            'girls_verified' => $girlsVerified,
            'leaders_verified' => $leadersVerified,
            'total_schools' => $totalSchools,
        ];
    }

    /**
     * Daftar pangkalan Gugus Depan untuk opsi filter dropdown
     */
    #[Computed]
    public function scoutGroupsList()
    {
        return ScoutGroup::orderBy('school_name')->get(['id', 'school_name', 'group_number']);
    }

    /**
     * Mengambil model peserta yang sedang dibuka di modal detail
     */
    #[Computed]
    public function selectedParticipant(): ?Participant
    {
        if (! $this->selectedParticipantId) {
            return null;
        }

        return Participant::with(['patrol.scoutGroup.user'])->find($this->selectedParticipantId);
    }

    public function render()
    {
        $query = Participant::query()
            ->with(['patrol.scoutGroup']);

        // Filter status: Terverifikasi (default) atau Semua
        if ($this->statusFilter === 'verified') {
            $query->where(function ($q) {
                $q->where('participants.status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('patrol.scoutGroup', function ($sg) {
                        $sg->where('verification_status', RegistrationStatusEnum::Verified);
                    });
            });
        }

        // Filter Gender / Kategori
        if ($this->genderFilter === 'male') {
            $query->where(function ($q) {
                $q->where('gender', 'male')
                    ->orWhereHas('patrol', fn ($pq) => $pq->where('gender_category', 'boys'));
            });
        } elseif ($this->genderFilter === 'female') {
            $query->where(function ($q) {
                $q->where('gender', 'female')
                    ->orWhereHas('patrol', fn ($pq) => $pq->where('gender_category', 'girls'));
            });
        }

        // Filter Jabatan dalam Regu
        if ($this->roleFilter !== 'all') {
            $query->where('role_in_patrol', $this->roleFilter);
        }

        // Filter Pangkalan Gudep
        if ($this->scoutGroupFilter !== 'all') {
            $query->whereHas('patrol', function ($pq) {
                $pq->where('scout_group_id', $this->scoutGroupFilter);
            });
        }

        // Pencarian Teks Bebas
        if (! empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                    ->orWhere('scout_id', 'like', $searchTerm)
                    ->orWhere('id_number', 'like', $searchTerm)
                    ->orWhereHas('patrol', function ($pq) use ($searchTerm) {
                        $pq->where('patrol_name', 'like', $searchTerm)
                            ->orWhereHas('scoutGroup', function ($sg) use ($searchTerm) {
                                $sg->where('school_name', 'like', $searchTerm)
                                    ->orWhere('group_number', 'like', $searchTerm);
                            });
                    });
            });
        }

        $participants = $query->orderBy('full_name', 'asc')->paginate($this->perPage);

        return view('livewire.admin.participants-directory', [
            'participants' => $participants,
        ]);
    }
}
