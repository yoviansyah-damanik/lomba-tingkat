@props([
    'class' => '',
    'showLabel' => false,
])

{{-- Toggle tema tanpa dependensi Alpine: handler terpasang langsung (instan, tidak menunggu Alpine/Livewire),
     ikon & label diganti murni via varian CSS dark:, dan transisi dimatikan sesaat agar repaint satu frame. --}}
@once
    <script>
        window.toggleTheme = () => {
            const root = document.documentElement;
            const target = root.classList.contains('dark') ? 'light' : 'dark';

            const freeze = document.createElement('style');
            freeze.textContent = '*,*::before,*::after{transition:none!important;animation-duration:0s!important}';
            document.head.appendChild(freeze);

            if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
                window.Flux.applyAppearance(target);
            } else {
                root.classList.toggle('dark', target === 'dark');
                try { localStorage.setItem('flux.appearance', target); } catch (e) {}
            }

            requestAnimationFrame(() => requestAnimationFrame(() => freeze.remove()));
        };
    </script>
@endonce

<button
    type="button"
    onclick="window.toggleTheme()"
    {{ $attributes->merge(['class' => 'group relative inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200/80 bg-white/80 p-2 text-zinc-600 shadow-xs transition-colors hover:border-amber-400 hover:bg-amber-50 hover:text-amber-700 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-300 dark:hover:border-amber-500/40 dark:hover:bg-zinc-800 dark:hover:text-amber-400 cursor-pointer ' . $class]) }}
    title="Ganti Mode Terang / Gelap"
    aria-label="Ganti Mode Terang / Gelap"
>
    {{-- Ikon Matahari (tampil saat Dark Mode untuk beralih ke Mode Terang) --}}
    <svg class="hidden size-4.5 text-amber-400 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    {{-- Ikon Bulan (tampil saat Light Mode untuk beralih ke Mode Gelap) --}}
    <svg class="block size-4.5 text-zinc-600 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>

    @if($showLabel)
        <span class="text-xs font-semibold select-none">
            <span class="hidden dark:inline">Mode Terang</span>
            <span class="dark:hidden">Mode Gelap</span>
        </span>
    @endif
</button>
