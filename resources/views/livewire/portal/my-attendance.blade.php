<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-official-header />

    <x-heading-section
        title="Presensi Saya"
        description="Jadwal kegiatan dan lomba yang berkaitan dengan Anda beserta status kehadirannya. Presensi dicatat oleh petugas pada saat kegiatan."
        badge="Presensi Kehadiran"
        badge-icon="calendar-days"
        badge-color="amber"
    />

    @if (! $identity)
        <div class="rounded-2xl border border-zinc-200 bg-white p-8 text-center shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <flux:icon name="exclamation-circle" class="mx-auto mb-2 size-10 text-zinc-400" />
            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Data presensi belum tersedia untuk akun ini</p>
            <p class="mt-1 text-xs text-zinc-500">Akun Anda belum tertaut ke data peserta, bindamping, atau aparatur. Hubungi Administrator.</p>
        </div>
    @else
        {{-- Identitas pengguna --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $identity['name'] }}</h2>
                <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">{{ $identity['role_label'] }}</p>
                <p class="text-[11px] text-zinc-500">{{ $identity['sub_title'] }}</p>
            </div>
            @if ($identity['id_number'])
                <x-id-badge :value="$identity['id_number']" />
            @endif
        </div>

        {{-- Jadwal & status kehadiran --}}
        <livewire:portal.my-attendance-list lazy />
    @endif
</div>
