<?php

namespace Tests\Concerns;

use App\Livewire\Portal\ChaperoneForm;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

trait SubmitsLivewireForms
{
    /**
     * Mengisi seluruh properti komponen dari payload lalu memanggil aksi simpan
     *
     * @param  class-string  $component
     * @param  array<string, mixed>  $mountParams
     * @param  array<string, mixed>  $payload
     */
    protected function submitForm(User $user, string $component, array $mountParams, array $payload, string $method = 'save'): Testable
    {
        $testable = Livewire::actingAs($user)->test($component, $mountParams);

        foreach ($payload as $property => $value) {
            $testable->set($property, $value);
        }

        return $testable->call($method);
    }

    /**
     * Menyimpan formulir bindamping baru; kategori dipilih lewat query ?gender= seperti pada tautan aplikasi
     *
     * @param  array<string, mixed>  $payload
     */
    protected function storeChaperone(User $user, array $payload): Testable
    {
        $testable = Livewire::withQueryParams(['gender' => $payload['gender'] ?? 'male'])
            ->actingAs($user)
            ->test(ChaperoneForm::class);

        foreach ($payload as $property => $value) {
            $testable->set($property, $value);
        }

        return $testable->call('save');
    }
}
