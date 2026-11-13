<?php

namespace App\Livewire\DigitalCard;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\DigitalCardService;
use Livewire\Attributes\Url;

/**
 * Cetak batch A4 seluruh ID Card aparatur (Panitia Pelaksana & Dewan Juri)
 */
class OfficialsBatch extends BatchCards
{
    #[Url]
    public string $role = 'all';

    public function mount(DigitalCardService $cards): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->isCommittee()), 403, 'Akses ditolak.');
    }

    protected function build(DigitalCardService $cards): array
    {
        $officials = User::with('roles')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', [RoleEnum::Committee->value, RoleEnum::Judge->value]))
            ->where('is_active', true)
            ->when($this->role === 'committee', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', RoleEnum::Committee->value)))
            ->when($this->role === 'judge', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', RoleEnum::Judge->value)))
            ->when($this->role === 'dual', function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', RoleEnum::Committee->value))
                    ->whereHas('roles', fn ($r) => $r->where('name', RoleEnum::Judge->value));
            })
            ->orderBy('name')
            ->get()
            ->sortBy(fn (User $u) => [
                $u->isJudge() && ! $u->isCommittee() ? 1 : 0,
                $u->name,
            ])
            ->values();

        $subtitle = match ($this->role) {
            'committee' => 'Khusus Panitia Pelaksana',
            'judge' => 'Khusus Dewan Juri Perlombaan',
            'dual' => 'Panitia Pelaksana Merangkap Dewan Juri',
            default => 'Seluruh Panitia Pelaksana & Dewan Juri Aktif',
        };

        return [
            'title' => 'ID Card Panitia Pelaksana & Dewan Juri (Susunan A4)',
            'subtitle' => $subtitle,
            'zipName' => 'KARTU_PANITIA_DAN_JURI_A4',
            'emptyMessage' => 'Belum ada aparatur panitia atau dewan juri aktif yang terdaftar.',
            'cards' => $officials->map(fn (User $u) => $cards->staffItem($u))->values(),
        ];
    }
}

