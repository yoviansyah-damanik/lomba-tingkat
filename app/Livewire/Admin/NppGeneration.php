<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Patrol;
use App\Services\PatrolNumberService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Pembuatan Nomor Pendaftaran Peserta (NPP) regu setelah pendaftaran ditutup.
 * NPP 2 digit (01, 02, dst.) diundi dan dinomori terpisah untuk putra dan putri.
 */
#[Layout('layouts.app', ['title' => 'Generate NPP'])]
#[Title('Generate NPP — Nomor Pendaftaran Peserta')]
class NppGeneration extends Component
{
    public bool $isReshuffleModalOpen = false;

    /**
     * Pendaftaran harus sudah ditutup agar jumlah regu peserta final
     */
    private function registrationClosed(): bool
    {
        return (bool) Event::first()?->isRegistrationClosed();
    }

    /**
     * Membuat NPP bagi regu terverifikasi yang belum bernomor
     */
    public function generate(PatrolNumberService $service): void
    {
        if (! $this->guard()) {
            return;
        }

        $result = $service->generate();
        $this->finish('npp_generated', $result);
    }

    public function openReshuffleModal(): void
    {
        $this->isReshuffleModalOpen = true;
    }

    /**
     * Mengundi ulang seluruh NPP (hanya selama belum ada penilaian juri)
     */
    public function reshuffle(PatrolNumberService $service): void
    {
        $this->isReshuffleModalOpen = false;

        if (! $this->guard()) {
            return;
        }

        if ($service->hasAssessments()) {
            Flux::toast(variant: 'danger', heading: 'Tidak Dapat Diundi Ulang', text: 'Penilaian juri sudah masuk sehingga NPP dikunci.');

            return;
        }

        $this->finish('npp_reshuffled', $service->generate(reshuffle: true));
    }

    private function guard(): bool
    {
        if ($this->registrationClosed()) {
            return true;
        }

        Flux::toast(variant: 'warning', heading: 'Pendaftaran Masih Dibuka', text: 'NPP hanya dapat dibuat setelah pendaftaran ditutup.');

        return false;
    }

    /**
     * @param  array<string, int>  $result
     */
    private function finish(string $action, array $result): void
    {
        AuditLog::log(
            action: $action,
            modelType: Patrol::class,
            modelId: Event::first()->id,
            newValues: $result,
            userId: Auth::id(),
        );

        $this->dispatch('npp-updated');

        Flux::toast(
            variant: 'success',
            heading: 'NPP Dibuat',
            text: "Putra: {$result['boys']} regu, Putri: {$result['girls']} regu."
        );
    }

    public function render(PatrolNumberService $service)
    {
        $event = Event::first();

        return view('livewire.admin.npp-generation', [
            'event' => $event,
            'registrationClosed' => $this->registrationClosed(),
            'summary' => $service->summary(),
            'hasAssessments' => $service->hasAssessments(),
        ]);
    }
}
