@props([
    'class' => '',
    'showLabel' => false,
])

<button
    type="button"
    x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            const observer = new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        toggleTheme() {
            this.isDark = !document.documentElement.classList.contains('dark');
            const target = this.isDark ? 'dark' : 'light';
            if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
                window.Flux.applyAppearance(target);
            } else {
                if (target === 'dark') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('flux.appearance', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('flux.appearance', 'light');
                }
            }
        }
    }"
    @click="toggleTheme()"
    {{ $attributes->merge(['class' => 'group relative inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200/80 bg-white/80 p-2 text-zinc-600 shadow-xs backdrop-blur-sm transition-all hover:border-amber-400 hover:bg-amber-50 hover:text-amber-700 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-300 dark:hover:border-amber-500/40 dark:hover:bg-zinc-800 dark:hover:text-amber-400 cursor-pointer ' . $class]) }}
    title="Ganti Mode Terang / Gelap"
    aria-label="Ganti Mode Terang / Gelap"
>
    {{-- Ikon Matahari (Aktif saat Dark Mode untuk beralih ke Mode Terang) --}}
    <svg x-show="isDark" class="size-4.5 text-amber-400 transition-transform duration-300 group-hover:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    {{-- Ikon Bulan (Aktif saat Light Mode untuk beralih ke Mode Gelap) --}}
    <svg x-show="!isDark" class="size-4.5 text-zinc-600 transition-transform duration-300 group-hover:-rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>

    @if($showLabel)
        <span x-text="isDark ? 'Mode Terang' : 'Mode Gelap'" class="text-xs font-semibold select-none"></span>
    @endif
</button>
