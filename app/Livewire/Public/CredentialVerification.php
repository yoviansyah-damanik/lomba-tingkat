<?php

namespace App\Livewire\Public;

use App\Services\QrCodeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman publik verifikasi akreditasi saat QR Code kartu digital dipindai
 */
#[Layout('layouts.standalone')]
class CredentialVerification extends Component
{
    public string $token;

    public function mount(string $token): void
    {
        $this->token = $token;
    }

    public function render(QrCodeService $qrService)
    {
        $data = $qrService->resolveToken($this->token);

        return view('livewire.public.credential-verification', [
            'data' => $data,
            'token' => $this->token,
            'qrDataUri' => $data ? $qrService->generateDataUri(route('digital-cards.verify', $this->token), 160) : null,
        ])->title(($data ? 'Verifikasi Kredensial - '.$data['name'] : 'QR Code Tidak Valid').' - LT III Padangsidimpuan');
    }
}
