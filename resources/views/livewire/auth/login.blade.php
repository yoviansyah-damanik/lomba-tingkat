{{-- Fallback tanpa JS/saat Livewire belum siap: kirim POST ke Fortify (bukan GET yang membocorkan kata sandi di URL) --}}
<form wire:submit="login" method="POST" action="{{ route('login.store') }}" class="space-y-4">
    @csrf

    {{-- Username Field --}}
    <div>
        <flux:input
            wire:model="username"
            name="username"
            label="Username"
            type="text"
            required
            autofocus
            autocomplete="username"
            autocapitalize="none"
            placeholder="Masukkan username Anda"
            icon="user"
        />
    </div>

    {{-- Password Field --}}
    <div class="space-y-1">
        <div class="flex items-center justify-between">
            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                Kata Sandi
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-800 hover:underline dark:text-amber-400 dark:hover:text-amber-300">
                    Lupa kata sandi?
                </a>
            @endif
        </div>
        <flux:input
            wire:model="password"
            name="password"
            type="password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            icon="lock-closed"
            viewable
        />
    </div>

    {{-- Remember Me Option --}}
    <div class="pt-0.5 flex items-center justify-between">
        <flux:checkbox wire:model="remember" name="remember" label="Ingat saya di perangkat ini" />
    </div>

    {{-- Submit Button --}}
    <div class="pt-2">
        <flux:button
            type="submit"
            variant="primary"
            icon="arrow-right-start-on-rectangle"
            data-test="login-button"
            wire:loading.attr="disabled"
            wire:target="login"
            class="w-full justify-center shadow-lg shadow-amber-600/25"
        >
            <span wire:loading.remove wire:target="login">Masuk ke Dasbor</span>
            <span wire:loading wire:target="login">Memproses...</span>
        </flux:button>
    </div>
</form>
