<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\QuestionnaireResponse;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire rekapitulasi kuisioner evaluasi Panitia & Dewan Juri
 */
#[Layout('layouts.app')]
#[Title('Rekapitulasi Kuisioner Evaluasi — Administrator')]
class QuestionnaireRecap extends Component
{
    use WithPagination;

    public ?Event $event = null;

    /** Filter peran responden: all, committee, judge */
    public string $roleFilter = 'all';

    /** Pencarian nama responden */
    public string $search = '';

    /** Modal detail respon */
    public bool $showDetailModal = false;

    public ?string $selectedResponseId = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isSuperadmin())) {
            abort(403, 'Akses terbatas untuk Administrator.');
        }

        $this->event = Event::where('is_active', true)->first() ?? Event::first();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Buka modal rincian respon kuisioner
     */
    public function openDetail(string $id): void
    {
        $this->selectedResponseId = $id;
        $this->showDetailModal = true;
    }

    /**
     * Tutup modal rincian
     */
    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedResponseId = null;
    }

    /**
     * Mengambil model respon terpilih
     */
    #[Computed]
    public function selectedResponse(): ?QuestionnaireResponse
    {
        if (! $this->selectedResponseId) {
            return null;
        }

        return QuestionnaireResponse::with('user')->find($this->selectedResponseId);
    }

    /**
     * Perhitungan statistik metrik agregat
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function stats(): array
    {
        if (! $this->event) {
            return [
                'total' => 0,
                'committee' => 0,
                'judge' => 0,
                'avg_overall' => 0.0,
                'avg_ui_ux' => 0.0,
                'avg_features' => 0.0,
                'avg_speed' => 0.0,
                'avg_transparency' => 0.0,
                'avg_combined' => 0.0,
                'positive_efficiency_rate' => 0,
                'devices' => [],
                'efficiency' => [],
                'obstacles' => [],
                'features' => [],
                'readiness' => [],
            ];
        }

        $allResponses = QuestionnaireResponse::where('event_id', $this->event->id)->get();
        $total = $allResponses->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'committee' => 0,
                'judge' => 0,
                'avg_overall' => 0.0,
                'avg_ui_ux' => 0.0,
                'avg_features' => 0.0,
                'avg_speed' => 0.0,
                'avg_transparency' => 0.0,
                'avg_combined' => 0.0,
                'positive_efficiency_rate' => 0,
                'devices' => [],
                'efficiency' => [],
                'obstacles' => [],
                'features' => [],
                'readiness' => [],
            ];
        }

        $committeeCount = $allResponses->whereIn('role', ['committee', 'both'])->count();
        $judgeCount = $allResponses->whereIn('role', ['judge', 'both'])->count();

        $avgOverall = round((float) $allResponses->avg('rating_overall'), 2);
        $avgUiUx = round((float) $allResponses->avg('rating_ui_ux'), 2);
        $avgFeatures = round((float) $allResponses->avg('rating_features'), 2);
        $avgSpeed = round((float) $allResponses->avg('rating_speed'), 2);
        $avgTransparency = round((float) $allResponses->avg('rating_transparency'), 2);

        $combinedAvg = round(($avgOverall + $avgUiUx + $avgFeatures + $avgSpeed + $avgTransparency) / 5, 2);

        // Efisiensi positif (sangat efisien + cukup membantu)
        $positiveCount = $allResponses->whereIn('efficiency_level', ['very_efficient', 'helpful'])->count();
        $positiveEfficiencyRate = round(($positiveCount / $total) * 100);

        // Helper perhitungan distribusi
        $calcDist = function (string $column, array $masterOptions) use ($allResponses, $total) {
            $grouped = $allResponses->groupBy($column)->map->count();
            $result = [];
            foreach ($masterOptions as $key => $label) {
                $count = (int) ($grouped[$key] ?? 0);
                $percent = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                $result[$key] = [
                    'label' => $label,
                    'count' => $count,
                    'percent' => $percent,
                ];
            }

            return $result;
        };

        return [
            'total' => $total,
            'committee' => $committeeCount,
            'judge' => $judgeCount,
            'avg_overall' => $avgOverall,
            'avg_ui_ux' => $avgUiUx,
            'avg_features' => $avgFeatures,
            'avg_speed' => $avgSpeed,
            'avg_transparency' => $avgTransparency,
            'avg_combined' => $combinedAvg,
            'positive_efficiency_rate' => $positiveEfficiencyRate,
            'devices' => $calcDist('device_used', QuestionnaireResponse::DEVICES),
            'efficiency' => $calcDist('efficiency_level', QuestionnaireResponse::EFFICIENCY_LEVELS),
            'obstacles' => $calcDist('main_obstacle', QuestionnaireResponse::OBSTACLES),
            'features' => $calcDist('most_useful_feature', QuestionnaireResponse::MOST_USEFUL_FEATURES),
            'readiness' => $calcDist('future_readiness', QuestionnaireResponse::READINESS_OPTIONS),
        ];
    }

    public function render()
    {
        $query = QuestionnaireResponse::with('user')
            ->where('event_id', $this->event?->id);

        if ($this->roleFilter === 'committee') {
            $query->whereIn('role', ['committee', 'both']);
        } elseif ($this->roleFilter === 'judge') {
            $query->whereIn('role', ['judge', 'both']);
        }

        if (! empty(trim($this->search))) {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('duty_assignment', 'like', $term)
                    ->orWhere('recommended_features', 'like', $term)
                    ->orWhere('experience_feedback', 'like', $term)
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('name', 'like', $term)
                            ->orWhere('scout_id', 'like', $term);
                    });
            });
        }

        $responses = $query->latest()->paginate(10);

        return view('livewire.admin.questionnaire-recap', [
            'responses' => $responses,
            'stats' => $this->stats,
        ]);
    }
}
