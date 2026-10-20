<x-layouts::app title="Pembina Pendamping (Bindamping)">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-official-header />

        <x-heading-section
            title="Pembina Pendamping (Bindamping)"
            description="Daftarkan pembina pendamping resmi yang mendampingi kontingen selama kegiatan LT III Kwarcab Padangsidimpuan."
            badge="Administrasi Kontingen"
            badge-icon="user-group"
            badge-color="amber"
        >
            <x-slot:actions>
                @if($scoutGroup->chaperones->count() > 0)
                    <flux:button href="{{ route('digital-cards.chaperone.batch', $scoutGroup->id) }}" target="_blank" variant="subtle" icon="qr-code">
                        Cetak Lembar ID Card (A4)
                    </flux:button>
                @endif
            </x-slot:actions>
        </x-heading-section>

        {{-- Notification Alerts --}}
        <x-alert />

        {{-- 2 Slot Resmi Bindamping Putra & Putri --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @php
                $maleChaperone = $scoutGroup->maleChaperone;
                $femaleChaperone = $scoutGroup->femaleChaperone;
            @endphp

            {{-- Slot 1: Bindamping Putra --}}
            <div class="rounded-2xl border {{ $maleChaperone ? 'border-blue-200 bg-white dark:border-blue-900/50' : 'border-dashed border-blue-300 bg-blue-50/40 dark:border-blue-900/40 dark:bg-blue-950/20' }} p-6 shadow-sm dark:bg-zinc-900 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex size-3 rounded-full bg-blue-600"></span>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                Pembina Pendamping Putra
                            </h3>
                        </div>
                        <span class="rounded-md font-mono text-[11px] font-bold border border-blue-200 bg-blue-50 px-2 py-0.5 text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300">
                            Gudep {{ $scoutGroup->male_group_number ?? $scoutGroup->group_number }}
                        </span>
                    </div>

                    @if($maleChaperone)
                        <div class="mt-4 flex items-start gap-4">
                            <div class="size-16 rounded-xl border border-zinc-200 bg-zinc-100 overflow-hidden shrink-0 dark:border-zinc-700 dark:bg-zinc-800 flex items-center justify-center">
                                @if($maleChaperone->photo_path)
                                    <img src="{{ asset('storage/' . $maleChaperone->photo_path) }}" class="size-full object-cover" alt="Foto">
                                @else
                                    <flux:icon name="user" class="size-8 text-zinc-400" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $maleChaperone->full_name }}
                                </h4>
                                <div class="mt-1 space-y-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    <p>No. WhatsApp: <strong class="text-zinc-800 dark:text-zinc-200">{{ $maleChaperone->phone_number }}</strong></p>
                                    <p>NTA Pramuka: <span class="font-mono">{{ $maleChaperone->scout_id ?: '-' }}</span></p>
                                </div>
                                <div class="mt-2.5">
                                    <span class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[11px] font-semibold {{ $maleChaperone->status?->badgeClasses() ?? 'bg-zinc-100 text-zinc-700' }}">
                                        {{ $maleChaperone->status?->label() ?? 'Draft' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <flux:icon name="user-plus" class="size-10 text-blue-400 mx-auto mb-2 opacity-80" />
                            <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Belum Ada Bindamping Putra</p>
                            <p class="text-[11px] text-zinc-400 mt-0.5">Maksimal 1 orang pembina pendamping putra per pangkalan</p>
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-2">
                    @if($maleChaperone)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('scout-group.chaperones.print', $maleChaperone->id) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400">
                                <flux:icon name="printer" class="size-3.5" />
                                Biodata
                            </a>
                            <span class="text-zinc-300">&bull;</span>
                            <a href="{{ route('digital-cards.chaperone', $maleChaperone->id) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-zinc-700 hover:text-zinc-900 dark:text-zinc-300">
                                <flux:icon name="qr-code" class="size-3.5" />
                                Kartu
                            </a>
                            @if($scoutGroup->isEditable())
                                <span class="text-zinc-300">&bull;</span>
                                <a href="{{ route('scout-group.chaperones.edit', $maleChaperone->id) }}"
                                    class="text-xs font-semibold text-zinc-700 hover:text-zinc-900 dark:text-zinc-300">
                                    Edit
                                </a>
                            @endif
                        </div>
                        @if($scoutGroup->isEditable())
                            <form method="POST" action="{{ route('scout-group.chaperones.destroy', $maleChaperone->id) }}" onsubmit="return confirm('Hapus Bindamping Putra ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    @elseif($scoutGroup->isEditable())
                        <a href="{{ route('scout-group.chaperones.create', ['gender' => 'male']) }}"
                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">
                            + Isi Formulir Biodata Bindamping Putra
                        </a>
                    @else
                        <span class="text-xs text-zinc-400 italic">Pendaftaran terkunci</span>
                    @endif
                </div>
            </div>

            {{-- Slot 2: Bindamping Putri --}}
            <div class="rounded-2xl border {{ $femaleChaperone ? 'border-rose-200 bg-white dark:border-rose-900/50' : 'border-dashed border-rose-300 bg-rose-50/40 dark:border-rose-900/40 dark:bg-rose-950/20' }} p-6 shadow-sm dark:bg-zinc-900 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex size-3 rounded-full bg-rose-600"></span>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                Pembina Pendamping Putri
                            </h3>
                        </div>
                        <span class="rounded-md font-mono text-[11px] font-bold border border-rose-200 bg-rose-50 px-2 py-0.5 text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300">
                            Gudep {{ $scoutGroup->female_group_number ?? $scoutGroup->group_number }}
                        </span>
                    </div>

                    @if($femaleChaperone)
                        <div class="mt-4 flex items-start gap-4">
                            <div class="size-16 rounded-xl border border-zinc-200 bg-zinc-100 overflow-hidden shrink-0 dark:border-zinc-700 dark:bg-zinc-800 flex items-center justify-center">
                                @if($femaleChaperone->photo_path)
                                    <img src="{{ asset('storage/' . $femaleChaperone->photo_path) }}" class="size-full object-cover" alt="Foto">
                                @else
                                    <flux:icon name="user" class="size-8 text-zinc-400" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $femaleChaperone->full_name }}
                                </h4>
                                <div class="mt-1 space-y-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    <p>No. WhatsApp: <strong class="text-zinc-800 dark:text-zinc-200">{{ $femaleChaperone->phone_number }}</strong></p>
                                    <p>NTA Pramuka: <span class="font-mono">{{ $femaleChaperone->scout_id ?: '-' }}</span></p>
                                </div>
                                <div class="mt-2.5">
                                    <span class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[11px] font-semibold {{ $femaleChaperone->status?->badgeClasses() ?? 'bg-zinc-100 text-zinc-700' }}">
                                        {{ $femaleChaperone->status?->label() ?? 'Draft' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <flux:icon name="user-plus" class="size-10 text-rose-400 mx-auto mb-2 opacity-80" />
                            <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Belum Ada Bindamping Putri</p>
                            <p class="text-[11px] text-zinc-400 mt-0.5">Maksimal 1 orang pembina pendamping putri per pangkalan</p>
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-2">
                    @if($femaleChaperone)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('scout-group.chaperones.print', $femaleChaperone->id) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400">
                                <flux:icon name="printer" class="size-3.5" />
                                Biodata
                            </a>
                            <span class="text-zinc-300">&bull;</span>
                            <a href="{{ route('digital-cards.chaperone', $femaleChaperone->id) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-zinc-700 hover:text-zinc-900 dark:text-zinc-300">
                                <flux:icon name="qr-code" class="size-3.5" />
                                Kartu
                            </a>
                            @if($scoutGroup->isEditable())
                                <span class="text-zinc-300">&bull;</span>
                                <a href="{{ route('scout-group.chaperones.edit', $femaleChaperone->id) }}"
                                    class="text-xs font-semibold text-zinc-700 hover:text-zinc-900 dark:text-zinc-300">
                                    Edit
                                </a>
                            @endif
                        </div>
                        @if($scoutGroup->isEditable())
                            <form method="POST" action="{{ route('scout-group.chaperones.destroy', $femaleChaperone->id) }}" onsubmit="return confirm('Hapus Bindamping Putri ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    @elseif($scoutGroup->isEditable())
                        <a href="{{ route('scout-group.chaperones.create', ['gender' => 'female']) }}"
                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                            + Isi Formulir Biodata Bindamping Putri
                        </a>
                    @else
                        <span class="text-xs text-zinc-400 italic">Pendaftaran terkunci</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Ringkasan Format Formulir Resmi --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-2">
                Format Lembar Biodata Resmi Pembina Pendamping
            </h4>
            <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Formulir biodata pembina pendamping mencakup: <strong>Identitas Diri & Pas Foto 3x4</strong>, <strong>Riwayat Pendidikan Formal</strong> (SD s/d S2), <strong>Riwayat Kepramukaan</strong> (Siaga s/d Pembina), <strong>Kursus Kepramukaan</strong> (KMD, KML, KPD, KPL), dan <strong>Pelatihan di Luar Kepramukaan</strong>. Setelah diisi, formulir dapat langsung dicetak sebagai berkas fisik resmi pendaftaran kontingen LT III.
            </p>
        </div>
    </div>
</x-layouts::app>
