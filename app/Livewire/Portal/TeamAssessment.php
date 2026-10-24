<?php

namespace App\Livewire\Portal;

use App\Models\Event;
use App\Models\Patrol;
use App\Services\StandingsService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman Penilaian Regu: nilai dan pencapaian regu bagi Peserta dan Bindamping
 */
#[Layout('layouts.app')]
#[Title('Penilaian Regu')]
class TeamAssessment extends Component
{
    #[Url(as: 'regu')]
    public string $patrolId = '';

    /**
     * Regu yang dapat dilihat: regu milik peserta, regu putra dan putri gugus depan,
     * atau regu gugus depan sesuai gender bindamping
     */
    #[Computed]
    public function patrols(): Collection
    {
        $user = auth()->user();

        if ($user->isParticipant() && $user->participant?->patrol_id) {
            return Patrol::with('scoutGroup')->whereKey($user->participant->patrol_id)->get();
        }

        // Gugus depan melihat regu putra dan putri pangkalannya
        if ($user->isScoutGroup() && $scoutGroup = $user->scoutGroup) {
            return Patrol::with('scoutGroup')
                ->where('scout_group_id', $scoutGroup->id)
                ->orderBy('gender_category')
                ->orderBy('patrol_name')
                ->get();
        }

        if ($user->isChaperone() && $chaperone = $user->chaperone) {
            $gender = $chaperone->gender === 'female' ? 'girls' : 'boys';

            return Patrol::with('scoutGroup')
                ->where('scout_group_id', $chaperone->scout_group_id)
                ->where('gender_category', $gender)
                ->orderBy('patrol_name')
                ->get();
        }

        return collect();
    }

    #[Computed]
    public function patrol(): ?Patrol
    {
        return $this->patrols->firstWhere('id', $this->patrolId) ?? $this->patrols->first();
    }

    public function render(StandingsService $standings)
    {
        $patrol = $this->patrol;
        $standing = null;
        $totalInCategory = 0;
        $scores = collect();

        if ($patrol) {
            $gender = $patrol->gender_category instanceof \BackedEnum ? $patrol->gender_category->value : $patrol->gender_category;
            $ranking = $standings->getPatrolStandings($gender, null, true);
            $standing = $ranking->firstWhere('id', $patrol->id);
            $totalInCategory = $ranking->count();

            // Hanya nilai yang sudah difinalisasi juri yang ditampilkan
            $scores = $patrol->competitionScores()
                ->with('competition')
                ->where('is_locked', true)
                ->get()
                ->sortBy(fn ($score) => $score->competition?->code)
                ->values();
        }

        return view('livewire.portal.team-assessment', [
            // Blind mode: peringkat umum disensor selama identitas regu disembunyikan
            'blindMode' => (bool) Event::first()?->hide_patrol_identities,
            'patrols' => $this->patrols,
            'patrol' => $patrol,
            'standing' => $standing,
            'totalInCategory' => $totalInCategory,
            'scores' => $scores,
        ]);
    }
}
