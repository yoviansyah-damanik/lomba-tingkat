<div class="py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('livewire.auth.partials.registration-header')

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
            <div class="lg:col-span-4">
                @include('livewire.auth.partials.registration-sidebar')
            </div>

            <div class="lg:col-span-8">
                @if (! $isRegistrationOpen)
                    @include('livewire.auth.partials.registration-closed')
                @else
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-start gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-xs">
                                <flux:icon name="key" class="size-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Masukkan Kode Undangan</h2>
                                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                    Registrasi akun pangkalan hanya untuk gugus depan yang telah didaftarkan panitia. Masukkan kode undangan 8 karakter yang diberikan Kwarcab untuk gugus depan Anda.
                                </p>
                            </div>
                        </div>

                        <form wire:submit="verifyCode" class="mt-6 space-y-4">
                            <flux:input wire:model="code" label="Kode Undangan" type="text" required autofocus
                                autocomplete="off" autocapitalize="characters" placeholder="Contoh: ABCD-EFGH" icon="key"
                                class="font-mono uppercase tracking-widest" />

                            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4 pt-2">
                                <flux:button href="{{ route('home') }}" wire:navigate variant="subtle" icon="arrow-left">
                                    Kembali ke Beranda
                                </flux:button>

                                <flux:button type="submit" variant="primary" icon="arrow-right" class="w-full sm:w-auto" data-test="verify-invitation-code-button">
                                    Lanjutkan Registrasi
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
