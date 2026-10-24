<?php

namespace Tests\Concerns;

use App\Livewire\Auth\InvitationCodeGate;
use App\Livewire\Auth\ScoutGroupRegistrationForm;
use App\Models\RegistrationInvitation;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

trait RegistersViaInvitation
{
    /**
     * Buat undangan untuk gugus depan lalu simpan ke sesi seolah kode sudah diverifikasi
     */
    protected function makeInvitation(array $attributes = [], bool $verified = true): RegistrationInvitation
    {
        $invitation = RegistrationInvitation::create($attributes + [
            'group_number' => '01-093 & 01-094',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'kwarran_name' => 'Kwarran Padangsidimpuan Utara',
        ]);

        if ($verified) {
            session([InvitationCodeGate::SESSION_KEY => ['id' => $invitation->id, 'at' => time()]]);
        }

        return $invitation;
    }

    /**
     * Daftarkan gugus depan lewat alur kode undangan.
     * Identitas pangkalan (school_name, group_number, kwarran) membentuk undangan; sisanya diisi di formulir.
     */
    protected function registerGudep(array $payload): Testable
    {
        $this->makeInvitation([
            'group_number' => $payload['group_number'] ?? '01-093',
            'school_name' => $payload['school_name'] ?? 'SMP Negeri 1 Padangsidimpuan',
            'kwarran_name' => $payload['kwarran'] ?? 'Kwarran Padangsidimpuan Utara',
        ]);

        return Livewire::test(ScoutGroupRegistrationForm::class)
            ->set('address', $payload['address'] ?? '')
            ->set('pic_name', $payload['pic_name'] ?? '')
            ->set('phone_number', $payload['phone_number'] ?? '')
            ->set('terms', (bool) ($payload['terms'] ?? false))
            ->call('register');
    }
}
