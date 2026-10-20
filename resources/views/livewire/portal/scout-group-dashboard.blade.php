<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-official-header />

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
            <a href="{{ route('scout-group.review') }}" wire:navigate class="hidden sm:inline-flex rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                Periksa Kelengkapan
            </a>
        </div>
    @endif

    {{-- Contingent Status Card --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <x-heading-section
            title="Portal Gugus Depan — {{ $scoutGroup->school_name }}"
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
                    <flux:button variant="subtle" icon="pencil-square" wire:click="openProfileModal">
                        Edit Profil
                    </flux:button>
                    <flux:button href="{{ route('scout-group.review') }}" wire:navigate variant="primary" icon="arrow-right">
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
                <strong class="font-bold flex items-center gap-1.5">
                    <flux:icon name="exclamation-triangle" class="size-4 text-orange-600" />
                    Catatan Perbaikan dari Administrator:
                </strong>
                <p class="mt-1 leading-relaxed pl-5.5">{{ $scoutGroup->revision_notes }}</p>
            </div>
        @endif
    </div>

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
                @if($boysPatrol)
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        Terdaftar
                    </span>
                @else
                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        Kosong
                    </span>
                @endif
            </div>

            <div class="mt-4">
                @if($boysPatrol)
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        Regu {{ $boysPatrol->patrol_name }}
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Anggota: <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $boysPatrol->participants->count() }} / 8</span> peserta
                    </p>

                    <div class="mt-3 flex items-center gap-2">
                        <div class="h-1.5 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($boysPatrol->participants->count() / 8) * 100 }}%"></div>
                        </div>
                        <span class="text-[11px] font-medium text-zinc-500">{{ round(($boysPatrol->participants->count() / 8) * 100) }}%</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('scout-group.patrols') }}" wire:navigate class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            Kelola Peserta &rarr;
                        </a>
                        @if($scoutGroup->canDownloadDocuments())
                        <a href="{{ route('scout-group.patrols.form-01', $boysPatrol->id) }}" target="_blank" class="rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            FORM 01
                        </a>
                        @endif
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-zinc-400">
                        Regu putra belum didaftarkan.
                    </div>
                    @if($scoutGroup->isEditable())
                        <div class="mt-2 text-center">
                            <a href="{{ route('scout-group.patrols') }}" wire:navigate class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                + Daftarkan Regu Putra
                            </a>
                        </div>
                    @endif
                @endif
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
                @if($girlsPatrol)
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        Terdaftar
                    </span>
                @else
                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        Kosong
                    </span>
                @endif
            </div>

            <div class="mt-4">
                @if($girlsPatrol)
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        Regu {{ $girlsPatrol->patrol_name }}
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Anggota: <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $girlsPatrol->participants->count() }} / 8</span> peserta
                    </p>

                    <div class="mt-3 flex items-center gap-2">
                        <div class="h-1.5 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            <div class="h-full bg-rose-600 rounded-full" style="width: {{ ($girlsPatrol->participants->count() / 8) * 100 }}%"></div>
                        </div>
                        <span class="text-[11px] font-medium text-zinc-500">{{ round(($girlsPatrol->participants->count() / 8) * 100) }}%</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('scout-group.patrols') }}" wire:navigate class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            Kelola Peserta &rarr;
                        </a>
                        @if($scoutGroup->canDownloadDocuments())
                        <a href="{{ route('scout-group.patrols.form-01', $girlsPatrol->id) }}" target="_blank" class="rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            FORM 01
                        </a>
                        @endif
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-zinc-400">
                        Regu putri belum didaftarkan.
                    </div>
                    @if($scoutGroup->isEditable())
                        <div class="mt-2 text-center">
                            <a href="{{ route('scout-group.patrols') }}" wire:navigate class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                + Daftarkan Regu Putri
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Bindamping --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Pembina Pendamping</span>
                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                    {{ $scoutGroup->chaperones->count() }} Orang
                </span>
            </div>

            <div class="mt-4 space-y-2">
                @forelse($scoutGroup->chaperones as $chaperone)
                    <div class="flex items-center justify-between rounded-xl border border-zinc-100 bg-zinc-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <div>
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $chaperone->name }}</div>
                            <div class="text-[11px] text-zinc-500">
                                {{ $chaperone->gender === 'male' ? 'Bindamping Putra' : 'Bindamping Putri' }}
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-zinc-400">{{ $chaperone->phone }}</span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-zinc-400">
                        Belum ada bindamping terdaftar.
                    </div>
                @endforelse

                <div class="pt-2">
                    <a href="{{ route('scout-group.chaperones') }}" wire:navigate class="block text-center rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        Kelola Bindamping &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Profil Pangkalan --}}
    <flux:modal wire:model="showProfileModal" class="max-w-xl">
        <form wire:submit="updateProfile" class="space-y-4">
            <div>
                <flux:heading size="lg">Edit Profil Pangkalan Gugus Depan</flux:heading>
                <flux:subheading>Pastikan nama pangkalan, nomor gudep, dan kontak PIC terisi akurat.</flux:subheading>
            </div>

            <flux:input wire:model="school_name" label="Nama Sekolah / Pangkalan *" placeholder="SMP Negeri 1 Padangsidimpuan" />
            <flux:input wire:model="group_number" label="Nomor Gudep *" placeholder="01-093 (otomatis menjadi 01-093 & 01-094)" />
            <flux:textarea wire:model="address" label="Alamat Pangkalan *" placeholder="Alamat lengkap pangkalan sekolah..." rows="2" />

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <flux:input wire:model="contact_person" label="Nama Kontak PIC *" placeholder="Nama Pembina Gudep" />
                <flux:input wire:model="contact_phone" label="Nomor HP / WhatsApp PIC *" placeholder="08123456789" />
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button type="button" variant="subtle" wire:click="$set('showProfileModal', false)">
                    Batal
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Simpan Profil
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
