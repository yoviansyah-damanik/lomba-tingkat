<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Panduan Pendaftaran Kontingen & Penggunaan Sistem
 */
#[Layout('layouts.public', ['title' => 'Panduan Pendaftaran & Penggunaan Sistem'])]
#[Title('Panduan Pendaftaran — LT III')]
class GuidePage extends Component
{
    public int $activeStep = 1;

    public function setStep(int $step): void
    {
        $this->activeStep = $step;
    }

    public function render()
    {
        return view('livewire.public.guide-page');
    }
}
