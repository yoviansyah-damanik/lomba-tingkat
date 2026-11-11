<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-heading-section
        title="Generate NPP"
        description="Nomor Pendaftaran Peserta (NPP) dibuat setelah pendaftaran ditutup. NPP 2 digit (01, 02, dst.) dinomori terpisah untuk putra dan putri, dan menjadi satu-satunya identitas regu yang dilihat juri."
        badge="Penjurian Berbasis NPP"
        badge-icon="hashtag"
        badge-color="indigo"
    />

    {{-- Status pendaftaran --}}
    @if ($registrationClosed)
        <div class="flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300" role="status">
            <flux:icon name="lock-closed" class="mt-0.5 size-4 shrink-0" />
            <span>Pendaftaran sudah ditutup{{ $event?->registration_end_at ? ' ('.$event->registration_end_at->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB)' : '' }}. NPP dapat dibuat.</span>
        </div>
    @else
        <div class="flex items-start gap-2 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200" role="status">
            <flux:icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0" />
            <span>Pendaftaran masih dibuka{{ $event?->registration_end_at ? ' hingga '.$event->registration_end_at->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : '' }}. NPP baru dapat dibuat setelah pendaftaran ditutup agar jumlah regu peserta sudah final.</span>
        </div>
    @endif

    {{-- Ringkasan per kategori --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach (['boys' => 'Regu Putra', 'girls' => 'Regu Putri'] as $gender => $label)
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">{{ $label }}</h3>
                <div class="mt-3 grid grid-cols-3 gap-3 text-center">
                    <div>
                        <p class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100">{{ $summary[$gender]['eligible'] }}</p>
                        <p class="text-[11px] text-zinc-500">Regu terverifikasi</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-emerald-600">{{ $summary[$gender]['numbered'] }}</p>
                        <p class="text-[11px] text-zinc-500">Sudah ber-NPP</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-amber-600">{{ $summary[$gender]['pending'] }}</p>
                        <p class="text-[11px] text-zinc-500">Belum ber-NPP</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Aksi --}}
    <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <flux:button wire:click="generate" variant="primary" icon="hashtag" :disabled="! $registrationClosed">
            Generate NPP
        </flux:button>
        <flux:button wire:click="openReshuffleModal" variant="subtle" icon="arrow-path" :disabled="! $registrationClosed || $hasAssessments">
            Undi Ulang Semua NPP
        </flux:button>
        <p class="text-xs text-zinc-500">
            @if ($hasAssessments)
                NPP terkunci karena penilaian juri sudah masuk. Regu baru yang terverifikasi tetap dapat diberi NPP lanjutan.
            @else
                Generate hanya memberi nomor lanjutan bagi regu yang belum ber-NPP; undi ulang mengacak seluruh nomor.
            @endif
        </p>
    </div>

    {{-- Daftar NPP (lazy) --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <livewire:admin.npp-list gender="boys" lazy />
        <livewire:admin.npp-list gender="girls" lazy />
    </div>

    <flux:modal wire:model="isReshuffleModalOpen" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Undi Ulang Semua NPP?</flux:heading>
            <flux:text>
                Seluruh NPP putra dan putri akan diacak ulang dan nomor lama tidak berlaku. Pastikan nomor lama belum dibagikan kepada regu atau juri.
            </flux:text>
            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('isReshuffleModalOpen', false)" variant="subtle">Batal</flux:button>
                <flux:button wire:click="reshuffle" variant="danger" icon="arrow-path">Undi Ulang</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
