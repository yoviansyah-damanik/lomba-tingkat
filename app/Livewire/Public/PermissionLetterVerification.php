<?php

namespace App\Livewire\Public;

use App\Models\PermissionRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman publik pembuktian keaslian surat izin (hasil pindai QR Ketua Panitia)
 */
#[Layout('layouts.standalone', ['title' => 'Verifikasi Surat Izin'])]
class PermissionLetterVerification extends Component
{
    public string $id;

    public function mount(string $id): void
    {
        $this->id = $id;
    }

    public function render()
    {
        return view('livewire.public.permission-letter-verification', [
            'permission' => PermissionRequest::with(['scoutGroup', 'members.participant', 'members.chaperone'])->find($this->id),
        ]);
    }
}
