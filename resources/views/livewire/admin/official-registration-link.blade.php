<div x-data="{ open: false, copied: false }" class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
    {{-- Header accordion --}}
    <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open"
        class="flex w-full cursor-pointer items-center justify-between gap-3 p-5 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-xs">
                <flux:icon name="link" class="size-5" />
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Tautan Pendaftaran Mandiri Aparatur</h3>
                <p class="text-xs text-zinc-500">Bagikan secara privat kepada calon panitia/juri. Tautan memuat kode rahasia dan tidak ditampilkan di halaman publik.</p>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            <span class="rounded-md px-2.5 py-1 text-[11px] font-bold {{ $isOpen ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                {{ $isOpen ? 'Dibuka' : 'Ditutup' }}
            </span>
            <flux:icon name="chevron-down" class="size-5 text-zinc-400 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
        </div>
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="space-y-4 border-t border-zinc-100 p-5 dark:border-zinc-800">
    @if ($url)
        <div class="flex flex-col gap-2 sm:flex-row">
            <input type="text" readonly value="{{ $url }}" onclick="this.select()"
                class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 font-mono text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
            <flux:button type="button" icon="clipboard-document" x-on:click="navigator.clipboard.writeText(@js($url)); copied = true; setTimeout(() => copied = false, 2000)">
                <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
            </flux:button>
            <flux:button type="button" variant="subtle" icon="arrow-path" wire:click="regenerate" wire:confirm="Buat kode baru? Tautan lama langsung tidak berlaku.">
                Kode Baru
            </flux:button>
        </div>
        @unless ($isOpen)
            <p class="text-[11px] text-zinc-500">Pendaftaran masih ditutup; aktifkan status dan atur jadwal di bawah sebelum membagikan tautan.</p>
        @endunless
    @else
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-dashed border-zinc-300 p-3 dark:border-zinc-700">
            <span class="text-xs text-zinc-500">Tautan belum dibuat.</span>
            <flux:button type="button" size="sm" variant="primary" icon="link" wire:click="regenerate">Buat Tautan Rahasia</flux:button>
        </div>
    @endif

    {{-- Jadwal & status buka-tutup --}}
    <form wire:submit="saveSettings" class="space-y-4 border-t border-zinc-100 pt-4 dark:border-zinc-800">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Dibuka Mulai (opsional):</label>
                <input type="datetime-local" wire:model="official_registration_start_at" class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                @error('official_registration_start_at') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Ditutup Pada (opsional):</label>
                <input type="datetime-local" wire:model="official_registration_end_at" class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                @error('official_registration_end_at') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center justify-between gap-4">
            <div>
                <strong class="block text-xs text-zinc-900 dark:text-zinc-100">Status Tautan Pendaftaran Aparatur</strong>
                <span class="text-[11px] text-zinc-400">Jika ditutup, tautan menampilkan pemberitahuan dan tidak menerima pendaftaran.</span>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
                <input type="checkbox" wire:model="is_official_registration_open" class="peer sr-only" />
                <div class="h-6 w-11 rounded-full bg-zinc-200 after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-zinc-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-amber-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-hidden dark:border-zinc-600 dark:bg-zinc-700"></div>
            </label>
        </div>

        <div class="flex justify-end">
            <flux:button type="submit" size="sm" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="saveSettings">Simpan Pengaturan</flux:button>
        </div>
    </form>
    </div>
</div>
