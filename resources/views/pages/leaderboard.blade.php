<x-layouts::public title="Klasemen & Live Rank">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Title --}}
        <x-heading-section
            class="mb-6"
            title="Klasemen Perolehan Prestasi"
            description="Peringkat resmi akumulasi Prestasi Tinggi, Prestasi Baik, dan Prestasi Cukup kontingen LT III."
        >
            <x-slot:breadcrumbs>
                <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
                <span class="mx-2">&rsaquo;</span>
                <span class="text-zinc-800 dark:text-zinc-200">Klasemen Resmi</span>
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-600 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Rank Terhubung</span>
                </div>
            </x-slot:actions>
        </x-heading-section>

        {{-- Achievement Tiers Explanation --}}
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-amber-300/80 bg-gradient-to-br from-amber-50 to-amber-100/40 p-4 text-amber-950 dark:border-amber-500/30 dark:from-amber-950/40 dark:to-zinc-900 dark:text-amber-200 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500 text-white font-bold text-xs">1</span>
                    <span class="font-bold text-sm">Prestasi Tinggi</span>
                </div>
                <p class="mt-2 text-xs text-amber-900/80 dark:text-amber-300/80 leading-relaxed">
                    Rank 1 pada setiap cabang mata lomba yang dipublikasikan.
                </p>
            </div>

            <div class="rounded-xl border border-zinc-300 bg-gradient-to-br from-zinc-50 to-zinc-100/60 p-4 text-zinc-900 dark:border-zinc-700 dark:from-zinc-900 dark:to-zinc-900/80 dark:text-zinc-200 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-zinc-400 text-white font-bold text-xs">2</span>
                    <span class="font-bold text-sm">Prestasi Baik</span>
                </div>
                <p class="mt-2 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Rank 2 pada setiap cabang mata lomba yang dipublikasikan.
                </p>
            </div>

            <div class="rounded-xl border border-amber-700/30 bg-gradient-to-br from-amber-100/30 to-amber-200/20 p-4 text-amber-950 dark:border-amber-700/30 dark:from-amber-950/20 dark:to-zinc-900 dark:text-amber-300 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-amber-700 text-white font-bold text-xs">3</span>
                    <span class="font-bold text-sm">Prestasi Cukup</span>
                </div>
                <p class="mt-2 text-xs text-amber-900/80 dark:text-amber-400/80 leading-relaxed">
                    Rank 3 pada setiap cabang mata lomba yang dipublikasikan.
                </p>
            </div>
        </div>

        {{-- Standings Placeholder / Waiting for Finalization --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-12 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 mb-4">
                <svg class="size-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 sm:text-lg">
                Klasemen Resmi Belum Dipublikasikan
            </h3>
            <p class="mx-auto mt-2 max-w-md text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                Hasil lomba akan ditampilkan secara otomatis melalui sistem Live Rank segera setelah penilaian masing-masing cabang lomba selesai dan disahkan oleh Administrator.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="{{ route('schedules') }}" wire:navigate class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-800 shadow-sm hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                    Lihat Jadwal Pertandingan
                </a>
            </div>
        </div>
    </div>
</x-layouts::public>
