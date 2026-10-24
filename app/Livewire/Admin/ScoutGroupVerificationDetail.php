<?php

namespace App\Livewire\Admin;

use App\Models\ScoutGroup;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Detail verifikasi berkas kontingen: data pangkalan, regu, peserta, bindamping, dan unggahan.
 * Keputusan verifikasi ada di komponen ScoutGroupVerificationActions yang memuat ulang halaman ini lewat event.
 */
#[Layout('layouts.app', ['title' => 'Detail Verifikasi Kontingen - Admin'])]
#[Title('Detail Verifikasi Kontingen — LT III')]
class ScoutGroupVerificationDetail extends Component
{
    public string $scoutGroupId;

    public function mount(string $id): void
    {
        $this->scoutGroupId = ScoutGroup::findOrFail($id)->id;
    }

    /**
     * Muat ulang tampilan saat keputusan diambil atau data kontingen berubah oleh pihak lain
     */
    #[On('verification-updated')]
    public function refreshDetail(): void
    {
        // Render ulang memuat data terbaru dari basis data
    }

    public function render()
    {
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones', 'user'])->findOrFail($this->scoutGroupId);

        return view('livewire.admin.scout-group-verification-detail', compact('scoutGroup'));
    }
}
