<x-layouts::app title="Portal Gugus Depan">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-official-header />

        @php
            $activeEvent = \App\Models\Event::where('is_active', true)->first();
        @endphp

        @if($activeEvent && $activeEvent->registration_end_at && $scoutGroup->isEditable())
            <div class="flex items-center justify-between rounded-2xl border border-amber-300/80 bg-amber-50/80 p-4 text-xs text-amber-950 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white font-bold">
                        <flux:icon name="clock" class="size-4" />
                    </div>
                    <div>
                        <span class="block font-bold">Batas Waktu Pengisian Regu, Peserta, & Bindamping:</span>
                        <span class="text-[11px] text-amber-900/80 dark:text-amber-300/80">
                            {{ $activeEvent->registration_end_at->translatedFormat('d F Y, H:i') }} WIB
                            &bull; Sisa waktu: <strong>{{ (int) ceil(now()->diffInDays($activeEvent->registration_end_at)) }} hari lagi</strong>. Pastikan seluruh berkas telah diajukan sebelum batas waktu ditutup.
                        </span>
                    </div>
                </div>
                <a href="{{ route('scout-group.review') }}" class="hidden sm:inline-flex rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                    Periksa Kelengkapan
                </a>
            </div>
        @endif

        {{-- Contingent Status Card --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <x-heading-section
                title="{{ $scoutGroup->school_name }}"
                badge="{{ $scoutGroup->registration_code }}"
                badge-color="amber"
            >
                <x-slot:badge-extra>
                    <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $scoutGroup->verification_status?->badgeClasses() }}">
                        {{ $scoutGroup->verification_status?->label() }}
                    </span>
                </x-slot:badge-extra>

                <x-slot:description>
                    Nomor Gudep: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $scoutGroup->group_number }}</span> &bull;
                    PIC: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $scoutGroup->contact_person }} ({{ $scoutGroup->contact_phone }})</span>
                </x-slot:description>

                <x-slot:actions>
                    @if($scoutGroup->isEditable())
                        <flux:button href="{{ route('scout-group.profile') }}" variant="subtle" icon="pencil-square">
                            Edit Profil
                        </flux:button>
                        <flux:button href="{{ route('scout-group.review') }}" variant="primary" icon="arrow-right">
                            Periksa & Ajukan
                        </flux:button>
                    @else
                        <span class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60">
                            Data Terkunci (Dalam Verifikasi)
                        </span>
                    @endif
                </x-slot:actions>
            </x-heading-section>

            {{-- Revision Notes Alert if any --}}
            @if($scoutGroup->verification_status === \App\Enums\RegistrationStatusEnum::NeedsRevision && $scoutGroup->revision_notes)
                <div class="mt-4 rounded-xl border border-orange-300 bg-orange-50/80 p-4 text-xs text-orange-900 dark:border-orange-500/30 dark:bg-orange-950/40 dark:text-orange-200">
                    <strong class="font-bold">Catatan Perbaikan dari Administrator:</strong>
                    <p class="mt-1 leading-relaxed">{{ $scoutGroup->revision_notes }}</p>
                </div>
            @endif
        </div>

        {{-- Notification Alerts --}}
        <x-alert />

        {{-- Summary Grid: Regu Putra, Regu Putri, Bindamping --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            {{-- Regu Putra --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-400">Regu Putra</span>
                        <span class="rounded bg-blue-100 px-1.5 py-0.5 font-mono text-[11px] font-bold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                            Gudep {{ $scoutGroup->male_group_number }}
                        </span>
                    </div>
                    <span class="rounded {{ $boysPatrol ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }} px-2 py-0.5 text-[10px] font-bold">
                        {{ $boysPatrol ? 'Terdaftar' : 'Belum Ada' }}
                    </span>
                </div>
                <div class="mt-3">
                    @if($boysPatrol)
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Regu {{ $boysPatrol->patrol_name }}</h3>
                        <p class="mt-1 text-xs text-zinc-500">Anggota: <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $boysPatrol->participantCount() }}</span> Peserta</p>
                        <p class="text-xs text-zinc-500">Pinru: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $boysPatrol->leader?->full_name ?? 'Belum ditentukan' }}</span></p>
                    @else
                        <p class="text-xs text-zinc-400 py-2">Belum membentuk regu penggalang putra.</p>
                    @endif
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                    <a href="{{ route('scout-group.patrols') }}" class="font-semibold text-blue-700 hover:underline dark:text-blue-400">
                        Kelola Regu Putra &rarr;
                    </a>
                </div>
            </div>

            {{-- Regu Putri --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">Regu Putri</span>
                        <span class="rounded bg-rose-100 px-1.5 py-0.5 font-mono text-[11px] font-bold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                            Gudep {{ $scoutGroup->female_group_number }}
                        </span>
                    </div>
                    <span class="rounded {{ $girlsPatrol ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }} px-2 py-0.5 text-[10px] font-bold">
                        {{ $girlsPatrol ? 'Terdaftar' : 'Belum Ada' }}
                    </span>
                </div>
                <div class="mt-3">
                    @if($girlsPatrol)
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Regu {{ $girlsPatrol->patrol_name }}</h3>
                        <p class="mt-1 text-xs text-zinc-500">Anggota: <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $girlsPatrol->participantCount() }}</span> Peserta</p>
                        <p class="text-xs text-zinc-500">Pinru: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $girlsPatrol->leader?->full_name ?? 'Belum ditentukan' }}</span></p>
                    @else
                        <p class="text-xs text-zinc-400 py-2">Belum membentuk regu penggalang putri.</p>
                    @endif
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                    <a href="{{ route('scout-group.patrols') }}" class="font-semibold text-rose-700 hover:underline dark:text-rose-400">
                        Kelola Regu Putri &rarr;
                    </a>
                </div>
            </div>

            {{-- Bindamping (Putra & Putri) --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Pembina Pendamping</span>
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        {{ $scoutGroup->chaperones->count() }}/2 Terdaftar
                    </span>
                </div>
                <div class="mt-3 space-y-2 text-xs">
                    <div class="flex items-start justify-between gap-2 rounded-lg bg-zinc-50 p-2 dark:bg-zinc-800/50">
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100">Bindamping Putra:</span>
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $scoutGroup->maleChaperone?->full_name ?? 'Belum ada' }}</span>
                        </div>
                        <span class="text-[10px] font-semibold {{ $scoutGroup->maleChaperone ? 'text-emerald-600' : 'text-zinc-400' }}">
                            {{ $scoutGroup->maleChaperone ? '✓ Siap' : 'Kosong' }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-2 rounded-lg bg-zinc-50 p-2 dark:bg-zinc-800/50">
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100">Bindamping Putri:</span>
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $scoutGroup->femaleChaperone?->full_name ?? 'Belum ada' }}</span>
                        </div>
                        <span class="text-[10px] font-semibold {{ $scoutGroup->femaleChaperone ? 'text-emerald-600' : 'text-zinc-400' }}">
                            {{ $scoutGroup->femaleChaperone ? '✓ Siap' : 'Kosong' }}
                        </span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                    <a href="{{ route('scout-group.chaperones') }}" class="font-semibold text-amber-700 hover:underline dark:text-amber-400">
                        Kelola Bindamping &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
