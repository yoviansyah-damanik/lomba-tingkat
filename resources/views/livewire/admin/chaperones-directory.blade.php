<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Direktori Pembina Pendamping (Bindamping)"
        description="Daftar resmi pembina pendamping putra dan putri pangkalan Gugus Depan yang telah diverifikasi untuk mendampingi kontingen LT III."
        badge="Data Resmi Kontingen"
        badge-icon="user-group"
        badge-color="amber"
    >
        <x-slot:actions>
            <flux:button href="{{ route('admin.participants.index') }}" wire:navigate variant="subtle" icon="academic-cap">
                Direktori Peserta
            </flux:button>
            <flux:button href="{{ route('admin.scout-groups.index') }}" wire:navigate variant="primary" icon="shield-check">
                Verifikasi Berkas
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Stats Cards Summary --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <x-stat-card
            label="Total Bindamping"
            :value="number_format($this->stats['total_verified'])"
            description="Lolos akreditasi resmi"
            color="amber"
            icon="check-badge"
        />

        <x-stat-card
            label="Pembina Putra"
            :value="number_format($this->stats['male_verified'])"
            description="Pendamping regu putra"
            color="blue"
            icon="user"
        />

        <x-stat-card
            label="Pembina Putri"
            :value="number_format($this->stats['female_verified'])"
            description="Pendamping regu putri"
            color="rose"
            icon="user"
        />

        <x-stat-card
            label="Kursus Pembina"
            :value="number_format($this->stats['trained_count'])"
            description="Memiliki data kursus"
            color="emerald"
            icon="academic-cap"
        />

        <x-stat-card
            label="Pangkalan Gudep"
            :value="number_format($this->stats['total_schools'])"
            description="Pangkalan SMP/MTs"
            color="neutral"
            icon="building-library"
            class="col-span-2 sm:col-span-1"
        />
    </div>

    {{-- Filter, Search & Controls --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            {{-- Search Bar --}}
            <div class="md:col-span-4">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                        <flux:icon name="magnifying-glass" class="size-4" />
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, NTA, ID, No. HP, pangkalan..."
                        class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 py-2 pl-9 pr-8 text-xs text-zinc-900 placeholder:text-zinc-400 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-white dark:focus:bg-zinc-900"
                    >
                    @if(!empty($search))
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                        >
                            <flux:icon name="x-mark" class="size-3.5" />
                        </button>
                    @endif
                </div>
            </div>

            {{-- Filter Gender / Kategori Dampingan --}}
            <div class="md:col-span-3">
                <select
                    wire:model.live="genderFilter"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-3 py-2 text-xs text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 dark:focus:bg-zinc-900"
                >
                    <option value="all">Semua Kategori Bindamping</option>
                    <option value="male">Pembina Putra (Damping Pa)</option>
                    <option value="female">Pembina Putri (Damping Pi)</option>
                </select>
            </div>

            {{-- Filter Pangkalan Gudep --}}
            <div class="md:col-span-3">
                <select
                    wire:model.live="scoutGroupFilter"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-3 py-2 text-xs text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 dark:focus:bg-zinc-900"
                >
                    <option value="all">Semua Pangkalan Gudep</option>
                    @foreach($this->scoutGroupsList as $group)
                        <option value="{{ $group->id }}">{{ $group->school_name }} ({{ $group->group_number }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status & Per Page --}}
            <div class="flex items-center gap-2 md:col-span-2">
                <select
                    wire:model.live="statusFilter"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-3 py-2 text-xs text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 dark:focus:bg-zinc-900"
                >
                    <option value="verified">Terverifikasi Saja</option>
                    <option value="all">Semua Status</option>
                </select>

                <select
                    wire:model.live="perPage"
                    class="w-20 rounded-xl border border-zinc-200 bg-zinc-50/50 px-2.5 py-2 text-xs text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 dark:focus:bg-zinc-900"
                    title="Jumlah baris per halaman"
                >
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        {{-- Active Filter Bar & Reset --}}
        @if(!empty($search) || $genderFilter !== 'all' || $scoutGroupFilter !== 'all' || $statusFilter !== 'verified')
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <div class="flex flex-wrap items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="font-medium">Filter Aktif:</span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                            Cari: "{{ $search }}"
                        </span>
                    @endif
                    @if($genderFilter !== 'all')
                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                            {{ $genderFilter === 'male' ? 'Pembina Putra' : 'Pembina Putri' }}
                        </span>
                    @endif
                    @if($statusFilter !== 'verified')
                        <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2 py-0.5 text-[11px] font-semibold text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                            Status: Semua
                        </span>
                    @endif
                </div>

                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 cursor-pointer"
                >
                    <flux:icon name="arrow-path" class="size-3" />
                    <span>Reset Filter</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Main Table of Chaperones --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-100 bg-zinc-50/75 text-zinc-500 uppercase tracking-wider dark:border-zinc-800 dark:bg-zinc-800/40">
                    <tr>
                        <th class="py-3 px-3.5 w-12 text-center">No</th>
                        <th class="py-3 px-3.5">Pembina Pendamping</th>
                        <th class="py-3 px-3.5">Pangkalan & Gudep</th>
                        <th class="py-3 px-3.5">Kategori Dampingan</th>
                        <th class="py-3 px-3.5">Kontak & Domisili</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($chaperones as $index => $chaperone)
                        <tr class="transition-colors hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                            {{-- Number --}}
                            <td class="py-3 px-3.5 text-center font-mono text-zinc-400">
                                {{ $chaperones->firstItem() + $index }}
                            </td>

                            {{-- Chaperone Identity --}}
                            <td class="py-3 px-3.5">
                                <div class="flex items-center gap-3">
                                    {{-- Photo Thumbnail --}}
                                    <div class="size-10 shrink-0 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 flex items-center justify-center">
                                        @if($chaperone->photo_path)
                                            <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover">
                                        @else
                                            <flux:icon name="user" class="size-5 text-zinc-400" />
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100 hover:text-amber-700 dark:hover:text-amber-400 cursor-pointer"
                                            wire:click="openDetail('{{ $chaperone->id }}')">
                                            {{ $chaperone->full_name }}
                                        </div>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-zinc-500 dark:text-zinc-400">
                                            <x-id-badge :value="$chaperone->id_number" />
                                            <span>NTA: <strong class="font-mono text-zinc-700 dark:text-zinc-300">{{ $chaperone->scout_id ?: '-' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- School & Scout Group --}}
                            <td class="py-3 px-3.5">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $chaperone->scoutGroup?->school_name ?? '-' }}
                                </div>
                                <x-id-badge label="Gudep" :value="$chaperone->scoutGroup?->registration_code" class="mt-0.5" />
                                <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-zinc-500 dark:text-zinc-400">
                                    <span>Gudep {{ $chaperone->group_number ?: ($chaperone->scoutGroup?->group_number ?? '-') }}</span>
                                    <span>&bull;</span>
                                    <span>Kwarran {{ $chaperone->scoutGroup?->kwarran_name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Gender Category Badge --}}
                            <td class="py-3 px-3.5">
                                @if($chaperone->isMale())
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-300">
                                        <span class="size-1.5 rounded-full bg-blue-600"></span>
                                        Pembina Putra
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-300">
                                        <span class="size-1.5 rounded-full bg-rose-600"></span>
                                        Pembina Putri
                                    </span>
                                @endif
                            </td>

                            {{-- Contact & Location --}}
                            <td class="py-3 px-3.5 text-zinc-600 dark:text-zinc-300">
                                <div class="font-medium flex items-center gap-1 text-zinc-800 dark:text-zinc-200">
                                    <flux:icon name="phone" class="size-3 text-zinc-400" />
                                    <span class="font-mono">{{ $chaperone->phone_number ?: ($chaperone->home_phone ?: '-') }}</span>
                                </div>
                                <div class="text-[11px] text-zinc-400 mt-0.5 truncate max-w-xs">
                                    {{ $chaperone->city ?: ($chaperone->home_address ?: '-') }}
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3 px-3.5 text-center">
                                @if($chaperone->status === \App\Enums\RegistrationStatusEnum::Verified || $chaperone->scoutGroup?->verification_status === \App\Enums\RegistrationStatusEnum::Verified)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 dark:border-emerald-700/50 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-0.5 text-[11px] font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $chaperone->status?->label() ?? 'Draft' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-3 px-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Quick Detail Modal Trigger --}}
                                    <button
                                        type="button"
                                        wire:click="openDetail('{{ $chaperone->id }}')"
                                        class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 shadow-2xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer"
                                        title="Buka Biodata Lengkap"
                                    >
                                        <flux:icon name="eye" class="size-3.5 text-zinc-500" />
                                        <span>Detail</span>
                                    </button>

                                    {{-- Cetak Biodata Formulir Resmi Pembina --}}
                                    @if($chaperone->scout_group_id)
                                        @if($chaperone->scoutGroup?->canDownloadDocuments())
                                        <a
                                            href="{{ route('admin.scout-groups.chaperones.print', ['scoutGroupId' => $chaperone->scout_group_id, 'chaperoneId' => $chaperone->id]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2 py-1 text-xs font-semibold text-amber-700 shadow-2xs hover:bg-amber-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-400 dark:hover:bg-zinc-700"
                                            title="Cetak Formulir Biodata Resmi Bindamping"
                                        >
                                            <flux:icon name="printer" class="size-3.5" />
                                            <span>Form</span>
                                        </a>
                                        @endif
                                    @endif

                                    {{-- Cetak Kartu Digital B2 Bindamping --}}
                                    @if($chaperone->scoutGroup?->canDownloadDocuments())
                                    <a
                                        href="{{ route('digital-cards.chaperone', $chaperone->id) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2 py-1 text-xs font-semibold text-zinc-700 shadow-2xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                                        title="Simpan Kartu Tanda Pengenal Bindamping (.PNG)"
                                    >
                                        <flux:icon name="qr-code" class="size-3.5" />
                                        <span>Kartu</span>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                    <flux:icon name="identification" class="size-6" />
                                </div>
                                <h3 class="mt-3 text-sm font-bold text-zinc-800 dark:text-zinc-200">
                                    Tidak ada data pembina pendamping ditemukan
                                </h3>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                                    @if(!empty($search) || $genderFilter !== 'all' || $scoutGroupFilter !== 'all')
                                        Coba sesuaikan kriteria pencarian atau bersihkan filter yang aktif.
                                    @else
                                        Belum ada data pembina pendamping yang terverifikasi oleh Administrator.
                                    @endif
                                </p>
                                @if(!empty($search) || $genderFilter !== 'all' || $scoutGroupFilter !== 'all' || $statusFilter !== 'verified')
                                    <div class="mt-4">
                                        <button
                                            type="button"
                                            wire:click="resetFilters"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-amber-700 cursor-pointer"
                                        >
                                            <flux:icon name="arrow-path" class="size-3.5" />
                                            Reset Filter
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($chaperones->hasPages())
            <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                {{ $chaperones->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Slide-Over / Modal Biodata Bindamping --}}
    @if($showDetailModal && $this->selectedChaperone)
        @php
            $c = $this->selectedChaperone;
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative w-full max-w-3xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 animate-in fade-in zoom-in-95 duration-200">
                {{-- Modal Header --}}
                <div class="flex items-start justify-between border-b border-zinc-100 p-6 dark:border-zinc-800 bg-gradient-to-r from-amber-500/10 via-transparent to-transparent">
                    <div class="flex items-center gap-4">
                        <div class="size-16 shrink-0 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 flex items-center justify-center">
                            @if($c->photo_path)
                                <img src="{{ asset('storage/' . $c->photo_path) }}" alt="{{ $c->full_name }}" class="size-full object-cover">
                            @else
                                <flux:icon name="user" class="size-8 text-zinc-400" />
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">
                                    {{ $c->full_name }}
                                </h2>
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $c->isMale() ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                                    {{ $c->isMale() ? 'Pembina Putra' : 'Pembina Putri' }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $c->scoutGroup?->school_name ?? '-' }}</span>
                                <span>&bull;</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">Gudep: {{ $c->scoutGroup?->group_number ?: '-' }}</span>
                                <span>&bull;</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">ID: {{ $c->id_number ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="closeDetail"
                        class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 cursor-pointer"
                    >
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="max-h-[70vh] overflow-y-auto p-6 space-y-6 text-xs">
                    {{-- 1. Identitas Pribadi --}}
                    <div>
                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider text-[11px] mb-3 text-amber-700 dark:text-amber-400">
                            1. Identitas Pribadi Pembina
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-2xl border border-zinc-100 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <div>
                                <span class="text-zinc-400 block">NTA Pramuka:</span>
                                <span class="font-bold text-zinc-800 dark:text-zinc-200 font-mono">{{ $c->scout_id ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block">No. WhatsApp / HP:</span>
                                <span class="font-bold text-zinc-800 dark:text-zinc-200 font-mono">{{ $c->phone_number ?: ($c->home_phone ?: '-') }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block">Tempat, Tanggal Lahir:</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $c->birth_place ?: '-' }}, {{ $c->birth_date?->translatedFormat('d F Y') ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block">Golongan Darah:</span>
                                <span class="font-bold text-zinc-800 dark:text-zinc-200 font-mono">{{ $c->blood_type ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block">Agama:</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $c->religion ?: '-' }}</span>
                            </div>
                            <div class="sm:col-span-3">
                                <span class="text-zinc-400 block">Alamat Domisili:</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $c->home_address ?: '-' }}, {{ $c->city ?: '-' }} {{ $c->postal_code }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Pangkalan Gugus Depan --}}
                    <div>
                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider text-[11px] mb-3 text-amber-700 dark:text-amber-400">
                            2. Data Pangkalan Gugus Depan
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-2xl border border-zinc-100 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <div>
                                <span class="text-zinc-400 block">Nama Pangkalan:</span>
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $c->scoutGroup?->school_name ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block">Nomor Gudep:</span>
                                <span class="font-bold text-zinc-800 dark:text-zinc-200 font-mono">{{ $c->group_number ?: ($c->scoutGroup?->group_number ?: '-') }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block">Wilayah Kwarran:</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $c->scoutGroup?->kwarran_name ?: '-' }}</span>
                            </div>
                            <div class="sm:col-span-3">
                                <span class="text-zinc-400 block">Alamat Pangkalan:</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $c->scoutGroup?->address ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Riwayat Kursus Kepramukaan --}}
                    @if(is_array($c->scout_trainings) && count($c->scout_trainings))
                        <div>
                            <h4 class="font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider text-[11px] mb-3 text-amber-700 dark:text-amber-400">
                                3. Riwayat Kursus Pembina Pramuka
                            </h4>
                            <div class="overflow-x-auto rounded-xl border border-zinc-100 dark:border-zinc-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="py-2 px-3">Kursus</th>
                                            <th class="py-2 px-3">Tahun</th>
                                            <th class="py-2 px-3">Lokasi / Tempat</th>
                                            <th class="py-2 px-3">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        @foreach($c->scout_trainings as $training)
                                            @if(!empty($training['course']) || !empty($training['year']))
                                                <tr>
                                                    <td class="py-2 px-3 font-bold text-zinc-900 dark:text-zinc-100">{{ $training['course'] ?? '-' }}</td>
                                                    <td class="py-2 px-3 text-zinc-600 dark:text-zinc-300 font-mono">{{ $training['year'] ?? '-' }}</td>
                                                    <td class="py-2 px-3 text-zinc-600 dark:text-zinc-300">{{ $training['location'] ?? '-' }}</td>
                                                    <td class="py-2 px-3 text-zinc-500">{{ $training['notes'] ?? '-' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- 4. Kredensial Digital & Kartu QR --}}
                    <div>
                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider text-[11px] mb-3 text-amber-700 dark:text-amber-400">
                            4. Kredensial Digital & Akses QR
                        </h4>
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl border border-amber-200/80 bg-amber-50/50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                            <div class="space-y-1">
                                <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                    UUID QR Verification Token:
                                </div>
                                <div class="font-mono text-[11px] text-amber-800 dark:text-amber-300 break-all">
                                    {{ $c->qr_token }}
                                </div>
                                <div class="text-[11px] text-zinc-500">
                                    Nomor Identitas Panitia: <span class="font-mono font-bold">{{ $c->id_number ?: '-' }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                @if($c->scoutGroup?->canDownloadDocuments())
                                <a
                                    href="{{ route('digital-cards.chaperone', $c->id) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-amber-700"
                                >
                                    <flux:icon name="qr-code" class="size-4" />
                                    <span>Tampilkan Kartu QR</span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between border-t border-zinc-100 p-4 px-6 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
                    <div>
                        @if($c->scout_group_id)
                            @if($c->scoutGroup?->canDownloadDocuments())
                            <a
                                href="{{ route('admin.scout-groups.chaperones.print', ['scoutGroupId' => $c->scout_group_id, 'chaperoneId' => $c->id]) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-2xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-400"
                            >
                                <flux:icon name="printer" class="size-4" />
                                <span>Cetak Formulir Biodata Resmi</span>
                            </a>
                            @endif
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="closeDetail"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-1.5 text-xs font-semibold text-zinc-700 shadow-2xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
