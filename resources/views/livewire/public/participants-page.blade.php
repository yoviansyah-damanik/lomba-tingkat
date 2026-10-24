<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8">
    {{-- Header Section --}}
    <x-heading-section
        title="Peserta & Pangkalan Kontingen"
        description="Daftar pangkalan Gugus Depan SMP/MTs yang terdaftar pada LT III Kwarcab Padangsidimpuan 2026. Pilih gugus depan untuk melihat nama seluruh peserta regu penggalang dan pembina pendamping."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Peserta Kontingen</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <flux:button
                    icon="trophy"
                    variant="subtle"
                    href="{{ route('leaderboard') }}"
                    wire:navigate
                    title="Lihat Klasemen Perolehan Prestasi"
                >
                    Klasemen
                </flux:button>
            </div>
        </x-slot:actions>
    </x-heading-section>

    {{-- Ringkasan Statistik --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card
            label="Gugus Depan"
            value="{{ $statistics['total_groups'] ?? 0 }}"
            description="Pangkalan SMP/MTs"
            icon="building-library"
            color="amber"
        />

        <x-stat-card
            label="Regu Penggalang"
            value="{{ $statistics['total_patrols'] ?? 0 }}"
            description="Regu Putra & Putri"
            icon="user-group"
            color="blue"
        />

        <x-stat-card
            label="Total Peserta"
            value="{{ $statistics['total_participants'] ?? 0 }}"
            description="Anggota Regu Terdaftar"
            icon="identification"
            color="emerald"
        />

        <x-stat-card
            label="Bindamping"
            value="{{ $statistics['total_chaperones'] ?? 0 }}"
            description="Pembina Pendamping"
            icon="academic-cap"
            color="indigo"
        />
    </div>

    {{-- Modal Rincian Seluruh Peserta & Bindamping saat Gudep Dipilih --}}
    <flux:modal
        wire:model="showDetailModal"
        wire:close="clearSelection"
        class="w-full max-w-4xl max-h-[92vh] sm:max-h-[90vh] overflow-y-auto p-4 sm:p-6"
    >
        @if($selectedGroup)
            <div class="space-y-6">
                {{-- Header Rincian Gudep --}}
                <div class="border-b border-zinc-100 pb-5 dark:border-zinc-800 pr-10 sm:pr-12">
                    <div class="space-y-1.5 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-0.5 text-xs font-bold text-white shadow-xs">
                                <flux:icon name="building-library" class="size-3.5" />
                                Gugus Depan Kontingen
                            </span>
                            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                {{ $selectedGroup->kwarran_name ?? 'Kwarcab Padangsidimpuan' }}
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-zinc-950 dark:text-zinc-50 leading-tight">
                            {{ $selectedGroup->school_name }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-zinc-600 dark:text-zinc-400">
                            <span>Gudep Putra: <strong class="font-mono text-zinc-800 dark:text-zinc-200">{{ $selectedGroup->male_group_number ?: ($selectedGroup->group_number ?: '-') }}</strong></span>
                            <span>&bull;</span>
                            <span>Gudep Putri: <strong class="font-mono text-zinc-800 dark:text-zinc-200">{{ $selectedGroup->female_group_number ?: ($selectedGroup->group_number ?: '-') }}</strong></span>
                            @if($selectedGroup->address)
                                <span class="hidden sm:inline">&bull;</span>
                                <span class="block sm:inline w-full sm:w-auto text-zinc-500 dark:text-zinc-400">{{ $selectedGroup->address }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Bagian 1: Pembina Pendamping (Bindamping) --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 dark:border dark:border-indigo-800/50">
                            <flux:icon name="academic-cap" class="size-4" />
                        </span>
                        <h4 class="text-sm sm:text-base font-bold text-zinc-900 dark:text-zinc-100">
                            Pembina Pendamping (Bindamping)
                        </h4>
                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:border dark:border-zinc-700/60">
                            {{ $selectedGroup->chaperones->count() }} Orang
                        </span>
                    </div>

                    @if($selectedGroup->chaperones->isNotEmpty())
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach($selectedGroup->chaperones as $chaperone)
                                @php
                                    $isMale = $chaperone->gender === 'male';
                                @endphp
                                <div class="rounded-2xl border {{ $isMale ? 'border-blue-200/80 bg-blue-50/40 dark:border-blue-800/50 dark:bg-blue-950/30' : 'border-rose-200/80 bg-rose-50/40 dark:border-rose-800/50 dark:bg-rose-950/30' }} p-3.5 sm:p-4 shadow-xs flex items-start gap-3 transition-colors">
                                    <div class="flex size-10 sm:size-11 shrink-0 items-center justify-center rounded-2xl {{ $isMale ? 'bg-blue-600 dark:bg-blue-500 text-white' : 'bg-rose-600 dark:bg-rose-500 text-white' }} font-bold text-xs sm:text-sm shadow-xs">
                                        {{ $isMale ? 'PA' : 'PI' }}
                                    </div>
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isMale ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200 dark:border dark:border-blue-800/60' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 dark:border dark:border-rose-800/60' }}">
                                                {{ $isMale ? 'Bindamping Putra' : 'Bindamping Putri' }}
                                            </span>
                                        </div>
                                        <h5 class="text-xs sm:text-sm font-extrabold text-zinc-900 dark:text-zinc-100 truncate">
                                            {{ $chaperone->full_name }}
                                        </h5>
                                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                                            <span>NTA: <strong class="font-mono text-zinc-700 dark:text-zinc-200">{{ $chaperone->scout_id ?: ($chaperone->id_number ?: '-') }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-zinc-200 p-6 text-center text-xs text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                            Belum ada data Pembina Pendamping yang didaftarkan.
                        </div>
                    @endif
                </div>

                {{-- Bagian 2: Regu Penggalang & Daftar Seluruh Peserta --}}
                <div class="space-y-5 pt-2">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        @foreach(['boys' => 'isBoys', 'girls' => 'isGirls'] as $category => $check)
                            @php $categoryPatrols = $selectedGroup->patrols->filter(fn ($p) => $p->{$check}())->values(); @endphp
                            <div class="space-y-5" wire:key="roster-{{ $category }}">
                                @forelse($categoryPatrols as $patrol)
                                    @include('livewire.public.partials.patrol-roster-card', ['patrol' => $patrol, 'category' => $category])
                                @empty
                                    @include('livewire.public.partials.patrol-roster-card', ['patrol' => null, 'category' => $category])
                                @endforelse
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    <span class="text-xs text-zinc-500 dark:text-zinc-400 text-center sm:text-left">
                        {{ $selectedGroup->school_name }} &bull; LT III Kwarcab Padangsidimpuan 2026
                    </span>
                    <div class="flex items-center gap-2 justify-end">
                        <flux:modal.close>
                            <flux:button
                                wire:click="clearSelection"
                                variant="filled"
                                class="w-full sm:w-auto"
                            >
                                Tutup
                            </flux:button>
                        </flux:modal.close>
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Kontrol Pencarian & Filter Kwarran --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            {{-- Pencarian Pintar --}}
            <div class="relative flex-1 max-w-lg">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari sekolah, nomor gudep, nama regu, peserta, atau bindamping..."
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2.5 text-xs text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800/90 dark:text-zinc-100 dark:focus:bg-zinc-900 dark:focus:border-amber-400"
                />
                @if($search)
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute right-3 top-2.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm cursor-pointer"
                    >
                        &times;
                    </button>
                @endif
            </div>

            {{-- Filter Kwartir Ranting --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 shrink-0">
                    Kwartir Ranting:
                </label>
                <select
                    wire:model.live="kwarranFilter"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800/90 dark:text-zinc-100 dark:focus:bg-zinc-900 dark:focus:border-amber-400"
                >
                    <option value="all">Seluruh Kwarran</option>
                    @foreach($kwarranList as $kw)
                        <option value="{{ $kw }}">{{ $kw }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex flex-wrap items-center gap-2">
            <span>Ditemukan <strong class="text-zinc-800 dark:text-zinc-200">{{ $groups->count() }}</strong> pangkalan Gugus Depan terdaftar.</span>
            <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
            <span>Klik pada pangkalan untuk menampilkan seluruh nama peserta & pendamping.</span>
        </div>
    </div>

    {{-- Grid Direktori Gugus Depan Terdaftar --}}
    @if($groups->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($groups as $group)
                @php
                    $isSelected = $selectedScoutGroupId === $group->id;
                    $boys = $group->patrols->firstWhere('gender_category', \App\Enums\PatrolGenderEnum::Boys);
                    $girls = $group->patrols->firstWhere('gender_category', \App\Enums\PatrolGenderEnum::Girls);
                    $totalParticipants = $group->patrols->sum(fn($p) => $p->participants->count());
                    $totalChaperones = $group->chaperones->count();
                @endphp
                <div
                    class="group relative rounded-2xl border transition-all duration-200 {{ $isSelected ? 'border-amber-500 bg-amber-50/20 ring-2 ring-amber-500 shadow-md dark:border-amber-500 dark:bg-amber-950/30 dark:ring-amber-500/60' : 'border-zinc-200 bg-white hover:border-amber-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700' }} p-5 flex flex-col justify-between"
                >
                    <div class="space-y-3.5">
                        {{-- Top Badge: Kwarran --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:border dark:border-zinc-700/60">
                                <flux:icon name="map-pin" class="size-3 text-amber-600 dark:text-amber-400" />
                                {{ $group->kwarran_name ?? 'Kwarcab Padangsidimpuan' }}
                            </span>
                        </div>

                        {{-- Nama Sekolah & Nomor Gudep --}}
                        <div>
                            <h4 class="text-base font-extrabold text-zinc-900 group-hover:text-amber-700 dark:text-zinc-100 dark:group-hover:text-amber-400 transition-colors line-clamp-1" title="{{ $group->school_name }}">
                                {{ $group->school_name }}
                            </h4>
                            <p class="mt-0.5 font-mono text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                Gudep {{ $group->male_group_number ?? $group->group_number }} &bull; {{ $group->female_group_number ?? $group->group_number }}
                            </p>
                        </div>

                        {{-- Ringkasan Regu --}}
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-2.5 dark:border-blue-800/40 dark:bg-blue-950/30">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">Regu Putra</span>
                                <strong class="font-extrabold text-zinc-900 dark:text-zinc-100 line-clamp-1">
                                    {{ $boys ? $boys->patrol_name : '-' }}
                                </strong>
                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $boys ? $boys->participants->count() . ' Peserta' : 'Belum ada' }}
                                </span>
                            </div>

                            <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-2.5 dark:border-rose-800/40 dark:bg-rose-950/30">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Regu Putri</span>
                                <strong class="font-extrabold text-zinc-900 dark:text-zinc-100 line-clamp-1">
                                    {{ $girls ? $girls->patrol_name : '-' }}
                                </strong>
                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $girls ? $girls->participants->count() . ' Peserta' : 'Belum ada' }}
                                </span>
                            </div>
                        </div>

                        {{-- Stat Badges --}}
                        <div class="flex items-center justify-between text-[11px] text-zinc-500 dark:text-zinc-400 pt-2 border-t border-zinc-100 dark:border-zinc-800/80">
                            <span class="flex items-center gap-1">
                                <flux:icon name="identification" class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                                <strong class="text-zinc-700 dark:text-zinc-200">{{ $totalParticipants }}</strong> Peserta
                            </span>
                            <span class="flex items-center gap-1">
                                <flux:icon name="academic-cap" class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                                <strong class="text-zinc-700 dark:text-zinc-200">{{ $totalChaperones }}</strong> Bindamping
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Aksi Pilih Gudep --}}
                    <div class="mt-4 pt-2">
                        <flux:button
                            wire:click="selectScoutGroup('{{ $group->id }}')"
                            variant="{{ $isSelected ? 'primary' : 'outline' }}"
                            icon="user-group"
                            class="w-full justify-center"
                        >
                            {{ $isSelected ? 'Sedang Ditampilkan' : 'Lihat Seluruh Peserta' }}
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-3xl border border-zinc-200 bg-white p-12 text-center shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 mb-4">
                <flux:icon name="magnifying-glass" class="size-7" />
            </div>
            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                Pangkalan Tidak Ditemukan
            </h3>
            <p class="mx-auto mt-1 max-w-sm text-xs text-zinc-500 dark:text-zinc-400">
                Tidak ada pangkalan Gugus Depan yang cocok dengan kata kunci pencarian "{{ $search }}".
            </p>
            <div class="mt-4">
                <flux:button wire:click="$set('search', '')" variant="subtle" icon="arrow-path">
                    Reset Pencarian
                </flux:button>
            </div>
        </div>
    @endif
</div>
