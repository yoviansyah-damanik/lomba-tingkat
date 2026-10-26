<?php

namespace App\Livewire\DigitalCard;

use App\Models\ScoutGroup;
use App\Services\DigitalCardService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Dasar tampilan batch ID Card (satu regu, bindamping, atau seluruh kontingen) beserta simpan ZIP/PNG.
 * Subkelas menyediakan kartu dan label halaman.
 */
#[Layout('layouts.digital-card')]
abstract class BatchCards extends Component
{
    public string $scoutGroupId;

    protected ScoutGroup $scoutGroup;

    /**
     * @return array{title: string, subtitle: string, zipName: string, emptyMessage: string, cards: Collection}
     */
    abstract protected function build(DigitalCardService $cards): array;

    protected function authorizeAndLoad(DigitalCardService $cards, ScoutGroup $scoutGroup): void
    {
        $cards->authorizeScoutGroup(auth()->user(), $scoutGroup);
        $this->scoutGroupId = $scoutGroup->id;
    }

    public function render(DigitalCardService $cards)
    {
        $page = $this->build($cards);

        return view('livewire.digital-card.batch', $page + ['scoutGroup' => $this->scoutGroup])->title($page['title']);
    }

    protected function slug(string ...$parts): string
    {
        return Str::slug(implode(' ', $parts));
    }
}
