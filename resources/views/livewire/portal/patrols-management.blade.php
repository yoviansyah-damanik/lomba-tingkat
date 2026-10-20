<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-official-header />

    <x-heading-section
        title="Pengelolaan Regu & Anggota Peserta"
        description="Bentuk regu penggalang putra dan putri, tentukan Pinru/Wapinru, dan daftarkan anggota regu."
        badge="Administrasi Kontingen"
        badge-icon="user-group"
        badge-color="amber"
    >
        <x-slot:actions>
            @if($scoutGroup->patrols->count() > 0)
                <flux:button href="{{ route('digital-cards.scout-group.batch', $scoutGroup->id) }}" target="_blank" variant="subtle" icon="qr-code">
                    ID Card Kontingen (.PNG / A4)
                </flux:button>
            @endif

            @if($scoutGroup->isEditable() && $scoutGroup->patrols->count() < 2)
                <flux:button type="button" variant="primary" icon="plus" wire:click="openCreateModal">
                    Bentuk Regu Baru
                </flux:button>
            @endif
        </x-slot:actions>
    </x-heading-section>

    {{-- Form Bentuk Regu Baru jika belum lengkap 2 regu (Putra & Putri) --}}
    @if($scoutGroup->isEditable() && $scoutGroup->patrols->count() < 2)
        <div id="form-buat-regu" class="rounded-2xl border border-amber-300/80 bg-gradient-to-r from-amber-50 to-white p-5 shadow-sm dark:border-amber-500/20 dark:from-zinc-900 dark:to-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">
                Bentuk Regu Baru
            </h3>
            <form wire:submit="storePatrol" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex-1">
                    <input
                        type="text"
                        name="patrol_name"
                        wire:model="patrol_name"
                        placeholder="Nama Regu (Contoh: Rajawali / Melati)"
                        required
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                    @error('patrol_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="w-full sm:w-48">
                    <select
                        name="gender_category"
                        wire:model.live="gender_category"
                        required
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        @if(!$scoutGroup->boysPatrol)
                            <option value="boys">Penggalang Putra</option>
                        @endif
                        @if(!$scoutGroup->girlsPatrol)
                            <option value="girls">Penggalang Putri</option>
                        @endif
                    </select>
                    @error('gender_category') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <flux:button
                    type="submit"
                    variant="primary"
                    icon="plus"
                    class="whitespace-nowrap"
                >
                    Buat Regu
                </flux:button>
            </form>
        </div>
    @endif

    {{-- Daftar Regu & Anggotanya --}}
    <div class="space-y-6">
        @forelse($scoutGroup->patrols as $patrol)
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800 sm:flex-row sm:items-center">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md {{ $patrol->isBoys() ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }} px-2.5 py-0.5 text-xs font-semibold">
                                {{ $patrol->gender_category?->label() }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-xs font-mono font-bold text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                Gudep {{ $patrol->group_number }}
                            </span>
                            <span class="text-xs text-zinc-400">Total: {{ $patrol->participantCount() }} Peserta</span>
                        </div>
                        <h3 class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                            Regu {{ $patrol->patrol_name }}
                        </h3>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 self-start sm:self-center">
                        <flux:button href="{{ route('digital-cards.patrol.batch', $patrol->id) }}" target="_blank" variant="subtle" size="sm" icon="qr-code">
                            ID Card Regu (A4 / .PNG)
                        </flux:button>

                        <flux:button href="{{ route('scout-group.patrols.form-01', $patrol->id) }}" target="_blank" variant="subtle" size="sm" icon="printer">
                            Cetak FORM : 01
                        </flux:button>

                        @if($scoutGroup->isEditable())
                            <flux:button href="{{ route('scout-group.participants.create', $patrol->id) }}" wire:navigate variant="primary" size="sm" icon="plus">
                                Tambah Peserta
                            </flux:button>

                            <flux:button
                                type="button"
                                wire:click="deletePatrol('{{ $patrol->id }}')"
                                wire:confirm="PERINGATAN: Apakah Anda yakin ingin menghapus Regu {{ $patrol->patrol_name }}? Tindakan ini akan menghapus seluruh data anggota peserta di dalam regu ini."
                                variant="danger"
                                size="sm"
                                icon="trash"
                            >
                                Hapus Regu
                            </flux:button>
                        @endif
                    </div>
                </div>

                {{-- Table Anggota Peserta --}}
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-100 bg-zinc-50/70 text-zinc-500 uppercase tracking-wider dark:border-zinc-800 dark:bg-zinc-800/40">
                            <tr>
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Peserta</th>
                                <th class="py-2.5 px-3">Jabatan Regu</th>
                                <th class="py-2.5 px-3">NTA Pramuka</th>
                                <th class="py-2.5 px-3">Tempat, Tgl Lahir</th>
                                <th class="py-2.5 px-3 text-center">Biodata</th>
                                @if($scoutGroup->isEditable())
                                    <th class="py-2.5 px-3 text-right">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($patrol->participants as $index => $participant)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50">
                                    <td class="py-3 px-3 text-zinc-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2.5">
                                            @if($participant->photo_path)
                                                <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="{{ $participant->full_name }}" class="size-8 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700">
                                            @else
                                                <div class="flex size-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                                    <flux:icon name="user" class="size-4" />
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $participant->full_name }}</div>
                                                <div class="text-[11px] text-zinc-400">Gol. Darah: {{ $participant->blood_type ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($participant->isLeader())
                                            <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                Pinru
                                            </span>
                                        @elseif($participant->isAssistantLeader())
                                            <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 font-medium text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                                Wapinru
                                            </span>
                                        @else
                                            <span class="text-zinc-500">Anggota</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 font-mono text-zinc-600 dark:text-zinc-400">
                                        <div>{{ $participant->scout_id ?: '-' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-zinc-600 dark:text-zinc-400">
                                        {{ $participant->birth_place }}, {{ $participant->birth_date?->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <flux:button href="{{ route('scout-group.participants.print', $participant->id) }}" target="_blank" variant="subtle" size="xs" icon="printer" title="Cetak Formulir Biodata Resmi">
                                                Form
                                            </flux:button>
                                            <flux:button href="{{ route('digital-cards.participant', $participant->id) }}" target="_blank" variant="subtle" size="xs" icon="qr-code" title="Cetak Kartu Peserta B2 (QR)">
                                                Kartu
                                            </flux:button>
                                        </div>
                                    </td>
                                    @if($scoutGroup->isEditable())
                                        <td class="py-3 px-3 text-right">
                                            <div class="inline-flex items-center gap-1.5 justify-end">
                                                <flux:button href="{{ route('scout-group.participants.edit', $participant->id) }}" wire:navigate variant="ghost" size="xs" icon="pencil-square">
                                                    Ubah
                                                </flux:button>
                                                <flux:button
                                                    type="button"
                                                    wire:click="deleteParticipant('{{ $participant->id }}')"
                                                    wire:confirm="Hapus peserta {{ $participant->full_name }}?"
                                                    variant="ghost"
                                                    size="xs"
                                                    icon="trash"
                                                    class="text-rose-600 hover:text-rose-700 dark:text-rose-400"
                                                >
                                                    Hapus
                                                </flux:button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-xs text-zinc-400">
                                        Belum ada peserta yang didaftarkan ke dalam regu ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-sm text-zinc-500 dark:border-zinc-800">
                Gugus Depan belum memiliki regu. Silakan buat regu putra atau putri di atas.
            </div>
        @endforelse
    </div>
</div>
