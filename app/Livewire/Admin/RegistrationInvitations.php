<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman kode undangan registrasi gugus depan; daftar dimuat lazy lewat komponen terpisah
 */
#[Layout('layouts.app')]
#[Title('Kode Undangan Registrasi')]
class RegistrationInvitations extends Component
{
    public function render()
    {
        return view('livewire.admin.registration-invitations');
    }
}
