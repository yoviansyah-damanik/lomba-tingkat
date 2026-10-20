<?php

use App\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $username = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->username = Auth::user()->username;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        abort_if($user->isManagedAccount(), 403);

        $validated = $this->validate($this->profileRules($user->id), \App\Models\User::usernameMessages());

        $user->fill($validated);
        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Profile settings') }}</flux:heading>

    @if (Auth::user()->isManagedAccount())
        <x-pages::settings.layout :heading="__('Profile')" subheading="Data akun mengikuti data gugus depan yang telah diverifikasi Administrator">
            <div class="my-6 w-full space-y-6">
                <flux:input :value="Auth::user()->name" :label="__('Name')" type="text" readonly disabled />
                <flux:input :value="Auth::user()->username" label="Username" type="text" readonly disabled />
                <flux:input :value="Auth::user()->institution" label="Pangkalan" type="text" readonly disabled />
                <flux:input :value="Auth::user()->position" label="Jabatan" type="text" readonly disabled />

                <flux:text variant="subtle" class="text-xs">
                    Nama, pangkalan, jabatan, dan kata sandi tidak dapat diubah sendiri. Hubungi pembina gugus depan atau Administrator jika data perlu diperbarui.
                </flux:text>
            </div>
        </x-pages::settings.layout>
    @else
    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and username')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <flux:input wire:model="username" label="Username" type="text" required autocomplete="username" />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

            </div>
        </form>

        <livewire:pages::settings.delete-user-form />
    </x-pages::settings.layout>
    @endif
</section>
