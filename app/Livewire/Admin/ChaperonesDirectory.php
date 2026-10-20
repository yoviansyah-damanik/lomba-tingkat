<?php

namespace App\Livewire\Admin;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\ScoutGroup;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Direktori & Manajemen Pembina Pendamping (Bindamping) untuk Administrator
 */
#[Layout('layouts.app')]
#[Title('Direktori Pembina Pendamping — Administrator LT III')]
class ChaperonesDirectory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $genderFilter = 'all'; // 'all', 'male', 'female'
    public string $scoutGroupFilter = 'all'; // 'all' or scout_group_id
    public string $statusFilter = 'verified'; // 'verified' (default), or 'all'
    public int $perPage = 15;

    // State Modal Slide-Over Biodata Detail Bindamping
    public bool $showDetailModal = false;
    public ?string $selectedChaperoneId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'genderFilter' => ['except' => 'all'],
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
     * Membuka modal detail biodata bindamping
     */
    public function openDetail(string $chaperoneId): void
    {
        $this->selectedChaperoneId = $chaperoneId;
        $this->showDetailModal = true;
    }

    /**
     * Menutup modal detail biodata bindamping
     */
    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedChaperoneId = null;
    }

    /**
     * Mereset seluruh filter ke nilai default
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'genderFilter', 'scoutGroupFilter', 'statusFilter']);
        $this->statusFilter = 'verified';
        $this->resetPage();
    }

    /**
     * Mengambil data statistik bindamping terverifikasi
     */
    #[Computed]
    public function stats(): array
    {
        $baseQuery = Chaperone::query()
            ->where(function ($q) {
                $q->where('chaperones.status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('scoutGroup', function ($sg) {
                        $sg->where('verification_status', RegistrationStatusEnum::Verified);
                    });
            });

        $totalVerified = (clone $baseQuery)->count();

        $maleVerified = (clone $baseQuery)
            ->where('chaperones.gender', 'male')
            ->count();

        $femaleVerified = (clone $baseQuery)
            ->where('chaperones.gender', 'female')
            ->count();

        // Menghitung jumlah bindamping yang telah memiliki riwayat kursus kepramukaan (KMD / KML / KPD)
        $trainedChaperones = (clone $baseQuery)
            ->whereNotNull('scout_trainings')
            ->count();

        $totalSchools = ScoutGroup::query()
            ->where(function ($sg) {
                $sg->where('verification_status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('chaperones', fn ($cq) => $cq->where('status', RegistrationStatusEnum::Verified));
            })
            ->count();

        return [
            'total_verified' => $totalVerified,
            'male_verified' => $maleVerified,
            'female_verified' => $femaleVerified,
            'trained_count' => $trainedChaperones,
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
     * Mengambil model bindamping yang sedang dibuka di modal detail
     */
    #[Computed]
    public function selectedChaperone(): ?Chaperone
    {
        if (! $this->selectedChaperoneId) {
            return null;
        }

        return Chaperone::with(['scoutGroup.user'])->find($this->selectedChaperoneId);
    }

    public function render()
    {
        $query = Chaperone::query()
            ->with(['scoutGroup']);

        // Filter status: Terverifikasi (default) atau Semua
        if ($this->statusFilter === 'verified') {
            $query->where(function ($q) {
                $q->where('chaperones.status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('scoutGroup', function ($sg) {
                        $sg->where('verification_status', RegistrationStatusEnum::Verified);
                    });
            });
        }

        // Filter Gender (Putra / Putri)
        if ($this->genderFilter === 'male') {
            $query->where('chaperones.gender', 'male');
        } elseif ($this->genderFilter === 'female') {
            $query->where('chaperones.gender', 'female');
        }

        // Filter Pangkalan Gudep
        if ($this->scoutGroupFilter !== 'all') {
            $query->where('chaperones.scout_group_id', $this->scoutGroupFilter);
        }

        // Pencarian Teks Bebas
        if (! empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('chaperones.full_name', 'like', $searchTerm)
                    ->orWhere('chaperones.scout_id', 'like', $searchTerm)
                    ->orWhere('chaperones.phone_number', 'like', $searchTerm)
                    ->orWhere('chaperones.id_number', 'like', $searchTerm)
                    ->orWhereHas('scoutGroup', function ($sg) use ($searchTerm) {
                        $sg->where('school_name', 'like', $searchTerm)
                            ->orWhere('group_number', 'like', $searchTerm);
                    });
            });
        }

        $chaperones = $query->orderBy('full_name', 'asc')->paginate($this->perPage);

        return view('livewire.admin.chaperones-directory', [
            'chaperones' => $chaperones,
        ]);
    }
}
