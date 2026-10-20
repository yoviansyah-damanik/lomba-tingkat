<?php

namespace App\Livewire\Admin;

use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\Event;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Pengelolaan Cabang Mata Lomba & Rubrik Kriteria Penilaian
 */
#[Layout('layouts.app')]
#[Title('Mata Lomba & Rubrik Penilaian — Livewire 4')]
class CompetitionsManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = 'all_categories';
    public string $participantTypeFilter = '';
    public string $publishedFilter = 'all';

    // State Modal Form Lomba
    public bool $showModal = false;
    public ?string $editingId = null;

    // Field Form Lomba
    public string $title = '';
    public string $code = '';
    public string $category = 'all';
    public string $participant_type = 'patrol';
    public int $min_participants = 8;
    public int $max_participants = 8;
    public string $scoring_formula = 'average';
    public string $description = '';
    public string $guidelines_text = '';
    public bool $is_published = true;

    // State Modal Rubrik Kriteria
    public bool $showCriteriaModal = false;
    public ?string $selectedCompetitionId = null;
    public string $criterionName = '';
    public string $criterionCode = '';
    public string $criterionDescription = '';
    public float $minScore = 0.0;
    public float $maxScore = 100.0;
    public float $weightPercentage = 100.0;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => 'all_categories'],
        'participantTypeFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedParticipantTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedParticipantType(): void
    {
        // Sesuaikan default min & max berdasarkan tipe format
        if ($this->participant_type === 'individual') {
            $this->min_participants = 1;
            $this->max_participants = 1;
        } elseif ($this->participant_type === 'group' && $this->min_participants === 8) {
            $this->min_participants = 3;
            $this->max_participants = 3;
        } elseif ($this->participant_type === 'patrol') {
            $this->min_participants = 8;
            $this->max_participants = 8;
        }
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => Competition::count(),
            'patrol' => Competition::whereIn('participant_type', ['patrol', 'full_patrol'])->count(),
            'group' => Competition::where('participant_type', 'group')->count(),
            'individual' => Competition::where('participant_type', 'individual')->count(),
            'published' => Competition::where('is_published', true)->count(),
        ];
    }

    public function getCompetitionsProperty()
    {
        $query = Competition::withCount(['criteria', 'schedules'])->orderBy('code');

        if ($this->categoryFilter !== 'all_categories') {
            $query->where('category', $this->categoryFilter);
        }

        if (! empty($this->participantTypeFilter)) {
            if ($this->participantTypeFilter === 'patrol') {
                $query->whereIn('participant_type', ['patrol', 'full_patrol']);
            } else {
                $query->where('participant_type', $this->participantTypeFilter);
            }
        }

        if ($this->publishedFilter === 'published') {
            $query->where('is_published', true);
        } elseif ($this->publishedFilter === 'draft') {
            $query->where('is_published', false);
        }

        if (! empty($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        return $query->paginate(12);
    }

    #[Computed]
    public function activeCompetition(): ?Competition
    {
        if (! $this->selectedCompetitionId) {
            return null;
        }

        return Competition::with('criteria')->find($this->selectedCompetitionId);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $this->resetValidation();
        $comp = Competition::findOrFail($id);

        $this->editingId = $comp->id;
        $this->title = $comp->title;
        $this->code = $comp->code;
        $this->category = $comp->category;
        $this->participant_type = $comp->participant_type;
        $this->min_participants = $comp->min_participants;
        $this->max_participants = $comp->max_participants;
        $this->scoring_formula = $comp->scoring_formula;
        $this->description = $comp->description ?? '';
        $this->guidelines_text = $comp->guidelines_text ?? '';
        $this->is_published = (bool) $comp->is_published;

        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:50',
                $this->editingId ? Rule::unique('competitions', 'code')->ignore($this->editingId) : 'unique:competitions,code',
            ],
            'category' => 'required|in:boys,girls,all',
            'participant_type' => 'required|in:patrol,group,individual',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'required|integer|min:1|gte:min_participants',
            'scoring_formula' => 'required|in:average,sum',
            'description' => 'nullable|string',
            'guidelines_text' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $data = $validated;
        $data['event_id'] = Event::where('is_active', true)->value('id') ?? Event::first()?->id;

        if ($this->editingId) {
            $comp = Competition::findOrFail($this->editingId);
            $comp->update($data);
            Flux::toast(variant: 'success', heading: 'Berhasil', text: "Mata lomba {$comp->title} berhasil diperbarui.");
        } else {
            $comp = Competition::create($data);
            Flux::toast(variant: 'success', heading: 'Berhasil', text: "Mata lomba {$comp->title} berhasil dibuat.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function togglePublish(string $id): void
    {
        $comp = Competition::findOrFail($id);
        $comp->is_published = ! $comp->is_published;
        $comp->save();

        $statusText = $comp->is_published ? 'dipublikasikan' : 'diarsipkan sebagai draft';
        Flux::toast(variant: 'info', heading: 'Publikasi Diperbarui', text: "Lomba {$comp->title} {$statusText}.");
    }

    public function deleteCompetition(string $id): void
    {
        $comp = Competition::findOrFail($id);
        $title = $comp->title;
        $comp->delete();

        Flux::toast(variant: 'danger', heading: 'Dihapus', text: "Mata lomba {$title} berhasil dihapus.");
    }

    /**
     * Buka modal pengelolaan rubrik kriteria penilaian
     */
    public function openCriteriaModal(string $competitionId): void
    {
        $this->selectedCompetitionId = $competitionId;
        $this->resetCriterionForm();
        $this->showCriteriaModal = true;
    }

    /**
     * Simpan kriteria rubrik baru secara instan
     */
    public function addCriterion(): void
    {
        $this->validate([
            'criterionName' => 'required|string|max:255',
            'criterionCode' => 'nullable|string|max:50',
            'criterionDescription' => 'nullable|string',
            'minScore' => 'required|numeric|min:0',
            'maxScore' => 'required|numeric|gt:minScore',
            'weightPercentage' => 'required|numeric|min:1|max:100',
        ]);

        if (! $this->selectedCompetitionId) {
            return;
        }

        $orderIndex = AssessmentCriterion::where('competition_id', $this->selectedCompetitionId)->count() + 1;

        AssessmentCriterion::create([
            'competition_id' => $this->selectedCompetitionId,
            'name' => $this->criterionName,
            'code' => $this->criterionCode ?: null,
            'description' => $this->criterionDescription ?: null,
            'min_score' => $this->minScore,
            'max_score' => $this->maxScore,
            'weight_percentage' => $this->weightPercentage,
            'order_index' => $orderIndex,
        ]);

        $this->resetCriterionForm();
        Flux::toast(variant: 'success', heading: 'Kriteria Ditambahkan', text: 'Kriteria rubrik penilaian berhasil disimpan.');
    }

    /**
     * Hapus kriteria rubrik
     */
    public function deleteCriterion(string $criterionId): void
    {
        $criterion = AssessmentCriterion::findOrFail($criterionId);
        $name = $criterion->name;
        $criterion->delete();

        Flux::toast(variant: 'danger', heading: 'Kriteria Dihapus', text: "Kriteria {$name} telah dihapus.");
    }

    private function resetCriterionForm(): void
    {
        $this->criterionName = '';
        $this->criterionCode = '';
        $this->criterionDescription = '';
        $this->minScore = 0.0;
        $this->maxScore = 100.0;
        $this->weightPercentage = 100.0;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->code = '';
        $this->category = 'all';
        $this->participant_type = 'patrol';
        $this->min_participants = 8;
        $this->max_participants = 8;
        $this->scoring_formula = 'average';
        $this->description = '';
        $this->guidelines_text = '';
        $this->is_published = true;
    }

    public function render()
    {
        return view('livewire.admin.competitions-management', [
            'competitions' => $this->competitions,
            'stats' => $this->stats,
            'activeCompetition' => $this->activeCompetition,
        ]);
    }
}
