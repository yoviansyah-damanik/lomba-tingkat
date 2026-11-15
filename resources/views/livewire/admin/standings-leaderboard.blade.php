<div class="space-y-6">
    {{-- Header Halaman Dasbor --}}
    <x-heading-section
        title="Rekap Nilai & Klasemen Prestasi"
        description="Pusat data hasil perlombaan, perolehan prestasi predikat resmi, dan klasemen juara umum LT III."
        icon="trophy"
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Rekap Nilai & Klasemen</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            @if($isEventMasked && $isAdmin)
                <flux:button
                    type="button"
                    variant="{{ $canUnmask ? 'primary' : 'outline' }}"
                    icon="{{ $canUnmask ? 'eye-slash' : 'eye' }}"
                    wire:click="toggleUnmask"
                    title="{{ $canUnmask ? 'Sembunyikan kembali identitas asli' : 'Hanya Administrator yang dapat mengintip identitas asli' }}"
                >
                    {{ $canUnmask ? 'Tutup Sensor (Admin)' : 'Intip Identitas (Admin)' }}
                </flux:button>
            @endif
            @if($isAdmin)
                <flux:button
                    icon="check-badge"
                    variant="subtle"
                    wire:click="openJudgeActionModal('finalize')"
                >
                    Finalisasi / Draf Juri
                </flux:button>
            @endif
            @if($canAccessCompetitionTab)
                <flux:button
                    type="button"
                    variant="subtle"
                    icon="printer"
                    wire:click="openBulkPrintModal"
                    title="Cetak seluruh atau pilihan cabang mata lomba sekaligus (Bulk)"
                >
                    Cetak Rekap Cabang (Bulk)
                </flux:button>
            @endif
            @if($activeTab === 'competitions' && $selectedCompetitionId)
                <flux:button href="{{ route('standings.competition.print', ['competition' => $selectedCompetitionId]) }}" target="_blank" variant="subtle" icon="printer">
                    Cetak Rekap Cabang
                </flux:button>
            @endif
            <flux:button href="{{ route('standings.print') }}" target="_blank" variant="subtle" icon="document-text">
                Pratinjau PDF Rekap
            </flux:button>
            <flux:button href="{{ route('leaderboard') }}" target="_blank" variant="primary" icon="arrow-top-right-on-square">
                Lihat Versi Publik
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    @if($isEventMasked)
        <div class="rounded-2xl border border-purple-200 bg-purple-50/70 p-4 text-xs text-purple-900 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex size-7 items-center justify-center rounded-xl bg-purple-600 text-white text-xs font-bold shrink-0">
                    🔒
                </span>
                <div>
                    <strong class="font-bold">Mode Sensor Identitas Aktif:</strong> Nama regu dan pangkalan disamarkan sesuai protokol Blind Scoring untuk panitia dan dewan juri.
                    @if($isAdmin)
                        <span class="block mt-0.5 text-[11px] text-purple-700 dark:text-purple-300 font-semibold">
                            (Anda login sebagai Administrator: Gunakan tombol 'Intip Identitas' untuk melihat nama asli).
                        </span>
                    @else
                        <span class="block mt-0.5 text-[11px] text-purple-700 dark:text-purple-300">
                            Hanya Administrator yang berwenang membuka sensor identitas asli peserta.
                        </span>
                    @endif
                </div>
            </div>
            @if($isAdmin)
                <flux:button
                    icon="{{ $canUnmask ? 'eye-slash' : 'eye' }}"
                    variant="{{ $canUnmask ? 'primary' : 'subtle' }}"
                    size="sm"
                    wire:click="toggleUnmask"
                >
                    {{ $canUnmask ? 'Sensor Aktif' : 'Intip Identitas' }}
                </flux:button>
            @endif
        </div>
    @endif

    {{-- Tabs Navigasi Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200 dark:border-zinc-800 pb-3">
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="$set('activeTab', 'patrols')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'patrols' ? 'bg-amber-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                <flux:icon name="user-group" class="size-4" />
                <span>Klasemen Regu</span>
            </button>
            @if($canAccessCompetitionTab)
                <button
                    type="button"
                    wire:click="$set('activeTab', 'competitions')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'competitions' ? 'bg-amber-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                    <flux:icon name="clipboard-document-list" class="size-4" />
                    <span>Rekap Nilai Mata Lomba</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ================= TAB 1: KLASEMEN REGU (PUTRA & PUTRI TERPISAH OTOMATIS) ================= --}}
    @if($activeTab === 'patrols')
        <div class="relative w-full sm:w-72">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari regu atau sekolah..."
                class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            />
        </div>

        {{-- Tab Kategori: Putra / Putri --}}
        <div class="flex flex-wrap items-center gap-2">
            <flux:button size="sm" icon="user-group" :variant="$genderTab === 'boys' ? 'primary' : 'subtle'" wire:click="$set('genderTab', 'boys')">
                Putra
                <span class="ms-1 rounded-full bg-white/25 px-1.5 text-[10px] font-bold">{{ $categoryCounts['boys'] }}</span>
            </flux:button>
            <flux:button size="sm" icon="user-group" :variant="$genderTab === 'girls' ? 'primary' : 'subtle'" wire:click="$set('genderTab', 'girls')">
                Putri
                <span class="ms-1 rounded-full bg-white/25 px-1.5 text-[10px] font-bold">{{ $categoryCounts['girls'] }}</span>
            </flux:button>
        </div>

        @php
            $genderMap = [
                'boys' => ['Regu Putra Berprestasi', 'bg-blue-500/15 text-blue-600 border-blue-500/30'],
                'girls' => ['Regu Putri Berprestasi', 'bg-rose-500/15 text-rose-600 border-rose-500/30'],
            ];
        @endphp

        <x-standings.rules />

        @foreach([$genderTab => $genderMap[$genderTab]] as $genderKey => [$genderTitle, $genderBadge])
            @php $group = $patrols->where('gender_category', $genderKey)->values(); @endphp

            <div class="rounded-2xl border border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden shadow-xs">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-2 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $genderBadge }}">{{ $genderKey === 'boys' ? 'Putra' : 'Putri' }}</span>
                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Klasemen {{ $genderTitle }}</span>
                    <span class="text-xs text-zinc-500">({{ $group->count() }} Regu)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 dark:text-zinc-400 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-3.5 text-center w-12">Rank</th>
                            <th class="py-3 px-3 text-center">NPP</th>
                            <th class="py-3 px-4">Nama Regu</th>
                            <th class="py-3 px-4">Pangkalan / Gudep</th>
                            <th class="py-3 px-3 text-center">Total Poin</th>
                            <th class="py-3 px-3 text-center text-amber-600 dark:text-amber-400">Tinggi (🥇)</th>
                            <th class="py-3 px-3 text-center text-slate-500 dark:text-slate-300">Baik (🥈)</th>
                            <th class="py-3 px-3 text-center text-amber-700 dark:text-amber-500">Cukup (🥉)</th>
                            <th class="py-3 px-3 text-center">Total Prestasi</th>
                        </tr>
                    </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 font-medium">
                        @forelse($group as $item)
                            <tr wire:key="standing-{{ $item['id'] }}" wire:click="openPatrolDetail('{{ $item['id'] }}')" title="Klik untuk melihat rincian poin" class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3 px-3.5 text-center">
                                    <span class="font-extrabold text-xs {{ $item['rank'] <= 3 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500' }}">
                                        #{{ $item['rank'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <x-npp-badge :npp="$item['npp'] ?? null" />
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm flex items-center gap-2">
                                        <span>{{ $item['patrol_name'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5">{{ $item['competitions_scored'] }} Lomba Dinilai</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $item['school_name'] }}</div>
                                    <div class="text-[10px] text-zinc-500">Gudep: {{ $item['group_number'] }}</div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-block rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-mono text-xs font-extrabold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300">{{ $item['total_points'] }}</span>
                                </td>
                                <td class="py-3 px-3 text-center font-extrabold text-amber-600 dark:text-amber-400 text-sm">
                                    {{ $item['gold'] }}
                                </td>
                                <td class="py-3 px-3 text-center font-extrabold text-slate-500 dark:text-slate-300 text-sm">
                                    {{ $item['silver'] }}
                                </td>
                                <td class="py-3 px-3 text-center font-extrabold text-amber-700 dark:text-amber-500 text-sm">
                                    {{ $item['bronze'] }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-800 dark:text-zinc-200">
                                    {{ $item['total_medals'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-zinc-500">
                                    Belum ada data regu terdaftar atau dinilai.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

    {{-- ================= TAB 2: REKAP NILAI MATA LOMBA ================= --}}
    @if($activeTab === 'competitions' && $canAccessCompetitionTab)
        <div class="space-y-4">
            {{-- Tab Kategori: Putra / Putri --}}
            <div class="flex flex-wrap items-center gap-2">
                <flux:button size="sm" icon="user-group" :variant="$genderTab === 'boys' ? 'primary' : 'subtle'" wire:click="$set('genderTab', 'boys')">
                    Putra
                    <span class="ms-1 rounded-full bg-white/25 px-1.5 text-[10px] font-bold">{{ $categoryCounts['boys'] }}</span>
                </flux:button>
                <flux:button size="sm" icon="user-group" :variant="$genderTab === 'girls' ? 'primary' : 'subtle'" wire:click="$set('genderTab', 'girls')">
                    Putri
                    <span class="ms-1 rounded-full bg-white/25 px-1.5 text-[10px] font-bold">{{ $categoryCounts['girls'] }}</span>
                </flux:button>
            </div>

            {{-- Selector Mata Lomba & Aksi --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-500 mb-1.5">
                        Pilih Cabang Mata Lomba:
                    </label>
                    <select
                        wire:model.live="selectedCompetitionId"
                        class="w-full md:w-96 rounded-xl border border-zinc-200 bg-zinc-50 p-2 text-xs font-semibold text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        @foreach($competitions as $c)
                            <option value="{{ $c->id }}">
                                [{{ $c->code }}] {{ $c->title }} ({{ $c->scores_count }} dinilai){{ ($c->total_assessments_count > 0 && $c->draft_assessments_count > 0) ? ' • ⚠️ Belum Final' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <flux:button
                        icon="printer"
                        variant="subtle"
                        wire:click="openBulkPrintModal"
                        title="Pilih dan cetak rekapitulasi nilai untuk beberapa atau seluruh cabang lomba sekaligus"
                    >
                        Cetak Masal (Bulk)
                    </flux:button>
                    @if($selectedCompetitionId)
                        <flux:button
                            icon="printer"
                            variant="subtle"
                            href="{{ route('standings.competition.print', ['competition' => $selectedCompetitionId]) }}"
                            target="_blank"
                            title="Cetak Rekap Nilai & Prestasi untuk cabang lomba ini"
                        >
                            Cetak Rekap Lomba
                        </flux:button>
                    @endif
                    @if($isAdmin)
                        <flux:button
                            icon="check-badge"
                            variant="subtle"
                            wire:click="openJudgeActionModal('finalize', null, '{{ $selectedCompetitionId }}')"
                            title="Kelola finalisasi atau kembalikan ke draf per juri pada cabang lomba ini"
                        >
                            Finalisasi / Draf Juri
                        </flux:button>
                    @endif
                    @if($canManageScores)
                        <flux:button
                            icon="arrow-path"
                            variant="subtle"
                            wire:click="recalculateRanks"
                            title="Urutkan kembali peringkat berdasarkan nilai akhir tertinggi"
                        >
                            Kalkulasi Ulang Rank
                        </flux:button>
                        @if($canInputScores)
                            <flux:button
                                icon="plus"
                                variant="primary"
                                wire:click="openScoreModal"
                            >
                                Input Nilai Regu
                            </flux:button>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Detail Mata Lomba Terpilih --}}
            @if($selectedComp)
                @php
                    $judgeStatus = $selectedComp->judgeScoringStatus();
                @endphp

                @if($judgeStatus['has_unfinalized'])
                    <div class="rounded-2xl border border-amber-300 bg-amber-50/80 p-4 text-xs text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex size-7 items-center justify-center rounded-xl bg-amber-600 text-white text-xs font-bold shrink-0">
                                <flux:icon name="exclamation-triangle" class="size-4" />
                            </span>
                            <div>
                                <strong class="font-bold">Penilaian Belum Final Sempurna:</strong> Terdapat <strong>{{ $judgeStatus['draft_count'] }} penilaian</strong> yang sudah diisi tetapi belum difinalisasi oleh dewan juri.
                                <span class="block mt-0.5 text-[11px] text-amber-700 dark:text-amber-300">
                                    Hasil klasemen sementara cabang ini belum mencerminkan nilai resmi dari seluruh dewan juri.
                                </span>
                            </div>
                        </div>
                        @if($isAdmin)
                            <flux:button
                                icon="check-badge"
                                variant="primary"
                                size="sm"
                                wire:click="openJudgeActionModal('finalize', null, '{{ $selectedComp->id }}')"
                            >
                                Finalisasi Semua Juri
                            </flux:button>
                        @endif
                    </div>
                @endif
                <div class="rounded-2xl border border-zinc-200/80 bg-gradient-to-r from-amber-50/40 via-white to-white p-4 dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-500 text-white">
                                {{ $selectedComp->code }}
                            </span>
                            <span class="text-xs text-zinc-500">• {{ $selectedComp->categoryLabel() }} • {{ $selectedComp->formatTypeLabel() }}</span>
                        </div>
                        <h2 class="text-lg font-black text-zinc-900 dark:text-zinc-100">
                            {{ $selectedComp->title }}
                        </h2>
                        @if($selectedComp->description)
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5 line-clamp-1">
                                {{ $selectedComp->description }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 text-xs text-zinc-500">
                        <div class="text-right">
                            <span class="block text-[10px] uppercase font-bold text-zinc-400">Rubrik Penilaian</span>
                            <span class="font-extrabold text-zinc-800 dark:text-zinc-200">{{ $selectedComp->criteria->count() }} Kriteria</span>
                        </div>
                        <div class="text-right border-l border-zinc-200 dark:border-zinc-800 pl-3">
                            <span class="block text-[10px] uppercase font-bold text-zinc-400">Formula Nilai</span>
                            <span class="font-extrabold text-zinc-800 dark:text-zinc-200 uppercase">{{ $selectedComp->scoring_formula }}</span>
                        </div>
                    </div>
                </div>

                {{-- Status Penilaian Dewan Juri Cabang Ini --}}
                @if($isAdmin && $competitionJudgesSummary->isNotEmpty())
                    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <flux:icon name="user-group" class="size-4 text-amber-600 dark:text-amber-400" />
                                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">
                                    Status Penilaian Dewan Juri Cabang Ini
                                </h3>
                            </div>
                            <span class="text-[11px] text-zinc-500">
                                {{ $competitionJudgesSummary->count() }} Dewan Juri Terlibat
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($competitionJudgesSummary as $js)
                                <div class="flex items-center justify-between rounded-xl border border-zinc-100 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                                    <div class="min-w-0 pr-2">
                                        <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100 truncate">
                                            {{ $js->name }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5 text-[10px]">
                                            <span class="inline-flex items-center gap-0.5 font-semibold text-emerald-700 dark:text-emerald-400">
                                                <flux:icon name="lock-closed" class="size-3" />
                                                {{ $js->finalized_count }} Terkunci
                                            </span>
                                            <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                            <span class="inline-flex items-center gap-0.5 font-medium text-amber-700 dark:text-amber-400">
                                                <flux:icon name="pencil" class="size-3" />
                                                {{ $js->draft_count }} Draf
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @if($js->draft_count > 0)
                                            <flux:button
                                                icon="check"
                                                size="xs"
                                                variant="primary"
                                                wire:click="openJudgeActionModal('finalize', '{{ $js->id }}', '{{ $selectedCompetitionId }}')"
                                                title="Finalisasi seluruh nilai draf juri ini"
                                            >
                                                Finalisasi
                                            </flux:button>
                                        @endif
                                        @if($js->finalized_count > 0)
                                            <flux:button
                                                icon="lock-open"
                                                size="xs"
                                                variant="danger"
                                                wire:click="openJudgeActionModal('draft', '{{ $js->id }}', '{{ $selectedCompetitionId }}')"
                                                title="Buka kunci seluruh nilai juri ini menjadi draf"
                                            >
                                                Draf
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Tabel Rekapitulasi Nilai Lomba --}}
                <div class="rounded-2xl border border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden shadow-xs">
                    <div class="p-3.5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-900/50">
                        <span class="font-bold text-xs uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                            Lembar Rekapitulasi Nilai Regu {{ $genderTab === 'boys' ? 'Putra' : 'Putri' }}
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-zinc-500">{{ $recap->count() }} Regu Dinilai</span>
                            @if($selectedComp)
                                <flux:button
                                    size="xs"
                                    icon="printer"
                                    variant="subtle"
                                    href="{{ route('standings.competition.print', ['competition' => $selectedComp->id]) }}"
                                    target="_blank"
                                    title="Cetak PDF Rekapitulasi Nilai {{ $selectedComp->title }}"
                                >
                                    Cetak PDF
                                </flux:button>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 dark:text-zinc-400 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                                <tr>
                                    <th class="py-3 px-3.5 text-center w-12">Rank</th>
                                    <th class="py-3 px-3 text-center">NPP</th>
                                    <th class="py-3 px-4">Nama Regu & Pangkalan</th>
                                    <th class="py-3 px-3 text-center">Kategori</th>
                                    <th class="py-3 px-4 text-center">Nilai Akhir</th>
                                    <th class="py-3 px-4 text-center">Predikat Prestasi</th>
                                    <th class="py-3 px-4">Juri Penilai & Catatan</th>
                                    <th class="py-3 px-3.5 text-center w-28">Status / Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 font-medium">
                                @forelse($recap as $score)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition {{ $score->rank === 1 ? 'bg-amber-50/20 dark:bg-amber-950/10' : '' }}">
                                        <td class="py-3 px-3.5 text-center">
                                            @if($score->rank === 1)
                                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-amber-500 text-white font-black text-xs shadow-xs">1</span>
                                            @elseif($score->rank === 2)
                                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-slate-400 text-white font-black text-xs">2</span>
                                            @elseif($score->rank === 3)
                                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-amber-700 text-white font-black text-xs">3</span>
                                            @else
                                                <span class="text-zinc-500 font-bold">{{ $score->rank ?: '-' }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <x-npp-badge :npp="$score->patrol?->npp" />
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                                {{ $score->patrol?->displayName($canUnmask) }}
                                            </div>
                                            <div class="text-[11px] text-zinc-500 mt-0.5">
                                                {{ $score->patrol?->displaySchool($canUnmask) }}
                                                @if(! $isEventMasked || $canUnmask)
                                                    (Gudep {{ $score->patrol?->group_number }})
                                                @else
                                                    (Gudep ***)
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($score->patrol?->isBoys())
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/15 text-blue-600 border border-blue-500/30">Putra</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30">Putri</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-mono text-base font-black text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($score->final_score, 2) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($score->hasAchievement())
                                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase border {{ $score->predicateBadgeClasses() }}">
                                                    {{ $score->predicateLabel() }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-xs text-zinc-600 dark:text-zinc-400">
                                            <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                                {{ ($isEventMasked && ! $canUnmask && ! $isAdmin) ? 'Dewan Juri' : ($score->judge?->name ?? 'Juri Pos') }}
                                            </div>
                                            @if($score->notes && (! $isEventMasked || $canUnmask || $isAdmin))
                                                <div class="text-[11px] text-zinc-500 italic mt-0.5 line-clamp-1">
                                                    "{{ $score->notes }}"
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3.5 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                @if($canManageScores)
                                                    @if($canInputScores)
                                                        <flux:button
                                                            icon="pencil-square"
                                                            variant="subtle"
                                                            size="sm"
                                                            wire:click="openScoreModal('{{ $score->patrol_id }}', '{{ $score->id }}')"
                                                            title="Ubah Nilai"
                                                        />
                                                    @endif
                                                    <flux:button
                                                        icon="{{ $score->is_locked ? 'lock-closed' : 'lock-open' }}"
                                                        variant="subtle"
                                                        size="sm"
                                                        wire:click="toggleScoreLock('{{ $score->id }}')"
                                                        title="{{ $score->is_locked ? 'Terkunci (Klik untuk buka)' : 'Draft (Klik untuk kunci)' }}"
                                                    />
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $score->is_locked ? 'border-emerald-300/60 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'border-zinc-300/60 bg-zinc-500/10 text-zinc-600 dark:text-zinc-400' }}">
                                                        <flux:icon :name="$score->is_locked ? 'lock-closed' : 'pencil'" class="size-3" />
                                                        {{ $score->is_locked ? 'Final' : 'Draft' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-zinc-500">
                                            <div class="max-w-xs mx-auto">
                                                <flux:icon name="clipboard-document-list" class="size-8 text-zinc-400 mx-auto mb-2" />
                                                <p class="font-semibold text-zinc-700 dark:text-zinc-300">Belum ada penilaian untuk cabang ini.</p>
                                                @if($canInputScores)
                                                    <flux:button
                                                        icon="plus"
                                                        variant="primary"
                                                        size="sm"
                                                        wire:click="openScoreModal"
                                                        class="mt-3"
                                                    >
                                                        Mulai Input Nilai
                                                    </flux:button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ================= MODAL RINCIAN POIN REGU ================= --}}
    @if($this->patrolDetail)
        @php
            $detail = $this->patrolDetail;
            $standing = $detail['standing'];
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs" wire:click.self="closePatrolDetail">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4 flex items-start justify-between gap-3 border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div>
                        <h3 class="flex items-center gap-2 text-base font-bold text-zinc-900 dark:text-zinc-100">
                            <flux:icon name="chart-bar" class="size-5 text-amber-600" />
                            Rincian Poin Regu {{ $standing['patrol_name'] }}
                            <x-npp-badge :npp="$standing['npp'] ?? null" :hide="$standing['is_masked'] ?? false" />
                        </h3>
                        <p class="mt-0.5 text-xs text-zinc-500">
                            {{ $standing['school_name'] }} &bull; Gudep {{ $standing['group_number'] }} &bull; Peringkat {{ ($isEventMasked && ! $canUnmask) ? '#•••' : '#'.$standing['rank'] }} {{ $standing['gender_category'] === 'boys' ? 'Putra' : 'Putri' }}
                        </p>
                    </div>
                    <flux:button type="button" wire:click="closePatrolDetail" variant="subtle" size="sm" icon="x-mark" />
                </div>

                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center dark:border-emerald-900/50 dark:bg-emerald-950/40">
                        <div class="font-mono text-xl font-extrabold text-emerald-800 dark:text-emerald-300">{{ $standing['total_points'] }}</div>
                        <div class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Total Poin</div>
                    </div>
                    <div class="rounded-xl border border-zinc-200 p-3 text-center dark:border-zinc-800">
                        <div class="text-xl font-extrabold text-amber-600 dark:text-amber-400">{{ $standing['gold'] }}</div>
                        <div class="text-[10px] font-bold uppercase text-zinc-500">Tinggi</div>
                    </div>
                    <div class="rounded-xl border border-zinc-200 p-3 text-center dark:border-zinc-800">
                        <div class="text-xl font-extrabold text-slate-500 dark:text-slate-300">{{ $standing['silver'] }}</div>
                        <div class="text-[10px] font-bold uppercase text-zinc-500">Baik</div>
                    </div>
                    <div class="rounded-xl border border-zinc-200 p-3 text-center dark:border-zinc-800">
                        <div class="text-xl font-extrabold text-amber-700 dark:text-amber-500">{{ $standing['bronze'] }}</div>
                        <div class="text-[10px] font-bold uppercase text-zinc-500">Cukup</div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead class="border-b border-zinc-200 bg-zinc-50 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60">
                            <tr>
                                <th class="px-3 py-2.5">Mata Lomba</th>
                                <th class="px-3 py-2.5 text-center">Nilai Akhir</th>
                                <th class="px-3 py-2.5 text-center">Peringkat</th>
                                <th class="px-3 py-2.5 text-center">Predikat</th>
                                <th class="px-3 py-2.5 text-center">Poin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse($detail['rows'] as $row)
                                <tr wire:key="detail-{{ $loop->index }}">
                                    <td class="px-3 py-2.5 font-semibold text-zinc-900 dark:text-zinc-100">{{ $row['competition'] }}</td>
                                    <td class="px-3 py-2.5 text-center font-mono">{{ $row['final_score'] !== null ? number_format((float) $row['final_score'], 2) : '-' }}</td>
                                    <td class="px-3 py-2.5 text-center font-bold">{{ ($isEventMasked && ! $canUnmask) ? '#•••' : ($row['rank'] ? '#'.$row['rank'] : '-') }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        <span class="rounded-md border px-2 py-0.5 text-[10px] font-bold {{ $row['predicate_classes'] }}">{{ $row['predicate_label'] }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono font-extrabold text-emerald-800 dark:text-emerald-300">{{ $row['points'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-zinc-500">Belum ada nilai lomba untuk regu ini.</td></tr>
                            @endforelse
                        </tbody>
                        @if($detail['rows']->isNotEmpty())
                            <tfoot class="border-t border-zinc-200 bg-zinc-50 font-bold dark:border-zinc-800 dark:bg-zinc-800/60">
                                <tr>
                                    <td colspan="4" class="px-3 py-2.5 text-right">Total Poin</td>
                                    <td class="px-3 py-2.5 text-center font-mono text-emerald-800 dark:text-emerald-300">{{ $detail['rows']->sum('points') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= MODAL INPUT / EDIT NILAI ================= --}}
    @if($showScoreModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800 mb-4">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <flux:icon name="pencil-square" class="size-5 text-amber-600" />
                        <span>Input Penilaian Regu Lomba</span>
                    </h3>
                    <button
                        type="button"
                        wire:click="closeScoreModal"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg cursor-pointer">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="saveScoreModal" class="space-y-4">
                    {{-- Pilihan Regu --}}
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Regu Penggalang Peserta <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model.live="modalPatrolId"
                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 p-2.5 text-xs font-semibold text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="">-- Pilih Regu Peserta --</option>
                            @foreach($eligiblePatrols as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->npp ? '['.$p->npp.'] ' : '' }}Regu {{ $p->patrol_name }} ({{ $p->scoutGroup?->school_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('modalPatrolId') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    {{-- Form Rincian Rubrik Penilaian Berbobot --}}
                    @if($selectedComp && $selectedComp->criteria->isNotEmpty())
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 p-3.5 bg-zinc-50/50 dark:bg-zinc-800/40 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wide">
                                    Rubrik Kriteria Penilaian
                                </span>
                                <span class="text-[10px] text-zinc-400">Skor 0 - 100</span>
                            </div>

                            @foreach($selectedComp->criteria as $criterion)
                                <div>
                                    <div class="flex justify-between items-center text-xs mb-1">
                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $criterion->name }}</span>
                                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">Bobot {{ $criterion->weight_percentage }}%</span>
                                    </div>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.5"
                                        wire:model="modalCriteriaScores.{{ $criterion->id }}"
                                        placeholder="Skor 0 - 100"
                                        class="w-full rounded-lg border border-zinc-200 bg-white p-2 text-xs font-bold text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Nilai Akhir Manual jika tidak ada rubrik --}}
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                Skor Nilai Akhir (0 - 100) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                min="0"
                                max="100"
                                step="0.1"
                                wire:model="modalFinalScore"
                                placeholder="Misal: 87.5"
                                class="w-full rounded-xl border border-zinc-200 bg-white p-2.5 text-xs font-bold text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            @error('modalFinalScore') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Catatan Penilaian --}}
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Catatan Juri / Catatan Pos
                        </label>
                        <textarea
                            rows="2"
                            wire:model="modalNotes"
                            placeholder="Catatan kelebihan atau kekurangan penampilan..."
                            class="w-full rounded-xl border border-zinc-200 bg-white p-2.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="closeScoreModal"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 transition cursor-pointer">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white transition shadow-xs cursor-pointer">
                            Simpan Nilai & Perbarui Rank
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Finalisasi / Draf Per Juri (Khusus Administrator) --}}
    @if($isJudgeActionModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $judgeActionType === 'finalize' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400' }}">
                        <flux:icon :name="$judgeActionType === 'finalize' ? 'check-badge' : 'lock-open'" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                            Kelola Status Penilaian Dewan Juri
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 leading-relaxed">
                            Finalisasi nilai draf menjadi sah dan terkunci, atau buka kunci nilai terfinalisasi kembali menjadi draf terbuka per dewan juri.
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    {{-- Pilihan Tindakan: Finalisasi vs Draf --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            Tindakan Status:
                        </label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $judgeActionType === 'finalize' ? 'bg-emerald-50/70 border-emerald-300 dark:bg-emerald-950/30' : '' }}">
                                <input type="radio" wire:model.live="judgeActionType" value="finalize" class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">Finalisasi Nilai (Kunci)</div>
                                    <div class="text-[11px] text-zinc-500">Ubah draf jadi sah & kunci</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $judgeActionType === 'draft' ? 'bg-rose-50/70 border-rose-300 dark:bg-rose-950/30' : '' }}">
                                <input type="radio" wire:model.live="judgeActionType" value="draft" class="text-rose-600 focus:ring-rose-500">
                                <div>
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">Ubah ke Draf (Buka Kunci)</div>
                                    <div class="text-[11px] text-zinc-500">Buka nilai agar juri bisa revisi</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Pilihan Dewan Juri --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Pilih Dewan Juri:
                        </label>
                        <flux:select wire:model.live="targetJudgeId">
                            <flux:select.option value="">-- Pilih Dewan Juri --</flux:select.option>
                            @foreach($allJudges as $j)
                                <flux:select.option value="{{ $j->id }}">{{ $j->name }} ({{ $j->username }})</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('targetJudgeId')
                            <span class="text-[11px] text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Pilihan Cakupan Cabang Lomba --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Cakupan Cabang Lomba:
                        </label>
                        <flux:select wire:model.live="targetCompetitionId">
                            <flux:select.option value="all">Semua Cabang Mata Lomba</flux:select.option>
                            @foreach($competitions as $c)
                                <flux:select.option value="{{ $c->id }}">{{ $c->code }} - {{ $c->title }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- Indikator Jumlah Data Terkena Dampak --}}
                    @if($targetJudgeId)
                        <div class="rounded-xl p-3 text-xs {{ $targetActionCount > 0 ? ($judgeActionType === 'finalize' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300' : 'bg-amber-50 border border-amber-200 text-amber-800 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-300') : 'bg-zinc-50 border border-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300' }}">
                            @if($targetActionCount > 0)
                                <div class="flex items-center gap-2 font-semibold">
                                    <flux:icon name="information-circle" class="size-4 shrink-0" />
                                    @if($judgeActionType === 'finalize')
                                        <span>Sebanyak <strong>{{ $targetActionCount }} penilaian draf</strong> akan difinalisasi dan dikunci ke klasemen.</span>
                                    @else
                                        <span>Sebanyak <strong>{{ $targetActionCount }} penilaian terfinalisasi</strong> akan dibuka kembali ke status Draf Terbuka.</span>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <flux:icon name="exclamation-triangle" class="size-4 shrink-0" />
                                    @if($judgeActionType === 'finalize')
                                        <span>Tidak ditemukan penilaian draf untuk juri & cabang lomba yang dipilih.</span>
                                    @else
                                        <span>Tidak ditemukan penilaian terfinalisasi untuk juri & cabang lomba yang dipilih.</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Alasan / Catatan --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ $judgeActionType === 'draft' ? 'Alasan Pembukaan Kunci ke Draf (Wajib Masuk Audit Log):' : 'Catatan Finalisasi (Opsional):' }}
                        </label>
                        <textarea
                            wire:model="judgeActionReason"
                            rows="3"
                            placeholder="{{ $judgeActionType === 'draft' ? 'Contoh: Revisi penilaian kriteria sesuai permohonan juri pos...' : 'Catatan opsional saat finalisasi...' }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                        ></textarea>
                        @error('judgeActionReason')
                            <span class="text-[11px] text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button
                        variant="subtle"
                        wire:click="$set('isJudgeActionModalOpen', false)"
                    >
                        Batal
                    </flux:button>
                    <flux:button
                        :variant="$judgeActionType === 'finalize' ? 'primary' : 'danger'"
                        :icon="$judgeActionType === 'finalize' ? 'check-badge' : 'lock-open'"
                        wire:click="executeJudgeAction"
                    >
                        {{ $judgeActionType === 'finalize' ? 'Finalisasi Nilai Juri' : 'Buka Kunci Nilai Juri' }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Cetak Rekap Prestasi Cabang Lomba (Bulk / Massal) --}}
    @if($showBulkPrintModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400">
                            <flux:icon name="printer" class="size-5" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                Cetak Rekap Prestasi Cabang Lomba (Bulk)
                            </h3>
                            <p class="mt-0.5 text-xs text-zinc-500 leading-relaxed">
                                Pilih cabang lomba yang ingin dicetak dalam berkas PDF resmi (menampilkan 3 Predikat Prestasi: Tinggi, Baik, dan Cukup).
                            </p>
                        </div>
                    </div>
                    <flux:button
                        size="xs"
                        variant="subtle"
                        icon="x-mark"
                        wire:click="closeBulkPrintModal"
                        title="Tutup Modal"
                    />
                </div>

                {{-- Filter Kategori & Tombol Aksi Cepat --}}
                <div class="flex flex-wrap items-center justify-between gap-2 border-y border-zinc-100 dark:border-zinc-800 py-2.5">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold text-zinc-500 mr-1">Kategori:</span>
                        <flux:button
                            size="xs"
                            :variant="$bulkFilterCategory === 'all' ? 'primary' : 'subtle'"
                            wire:click="$set('bulkFilterCategory', 'all')"
                        >
                            Semua
                        </flux:button>
                        <flux:button
                            size="xs"
                            :variant="$bulkFilterCategory === 'boys' ? 'primary' : 'subtle'"
                            wire:click="$set('bulkFilterCategory', 'boys')"
                        >
                            Putra
                        </flux:button>
                        <flux:button
                            size="xs"
                            :variant="$bulkFilterCategory === 'girls' ? 'primary' : 'subtle'"
                            wire:click="$set('bulkFilterCategory', 'girls')"
                        >
                            Putri
                        </flux:button>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="subtle" wire:click="selectAllBulkCompetitions">
                            Pilih Semua
                        </flux:button>
                        <flux:button size="xs" variant="subtle" wire:click="deselectAllBulkCompetitions">
                            Batal Pilih
                        </flux:button>
                    </div>
                </div>

                {{-- Indikator Jumlah Terpilih --}}
                <div class="flex items-center justify-between text-xs px-1 text-zinc-600 dark:text-zinc-400">
                    <span>
                        <strong>{{ count($selectedBulkCompetitionIds) }}</strong> dari <strong>{{ $filteredBulkCompetitions->count() }}</strong> cabang lomba dipilih
                    </span>
                    @if(count($selectedBulkCompetitionIds) === 0)
                        <span class="text-rose-500 font-semibold text-[11px]">* Pilih minimal 1 cabang lomba</span>
                    @endif
                </div>

                {{-- Daftar Pilihan Cabang Lomba --}}
                <div class="max-h-64 sm:max-h-72 overflow-y-auto space-y-1.5 pr-1 divide-y divide-zinc-100 dark:divide-zinc-800/60 rounded-xl border border-zinc-200/80 bg-zinc-50/50 p-2 dark:border-zinc-800 dark:bg-zinc-950/40">
                    @forelse($filteredBulkCompetitions as $c)
                        @php
                            $isSelected = in_array((string) $c->id, $selectedBulkCompetitionIds, true);
                        @endphp
                        <label wire:key="bulk-comp-{{ $c->id }}" class="flex items-center justify-between gap-3 p-2 rounded-lg cursor-pointer transition hover:bg-white dark:hover:bg-zinc-900 {{ $isSelected ? 'bg-amber-50/70 border border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/40' : '' }}">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <input
                                    type="checkbox"
                                    value="{{ $c->id }}"
                                    wire:model.live="selectedBulkCompetitionIds"
                                    class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800"
                                >
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                            {{ $c->code }}
                                        </span>
                                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate">
                                            {{ $c->title }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-zinc-500 mt-0.5">
                                        {{ $c->categoryLabel() }} &bull; Formula {{ $c->scoring_formula }}
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $c->scores_count > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    {{ $c->scores_count }} dinilai
                                </span>
                            </div>
                        </label>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-500">
                            Tidak ditemukan cabang lomba pada kategori ini.
                        </div>
                    @endforelse
                </div>

                {{-- Tombol Aksi Bawah --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="subtle" wire:click="closeBulkPrintModal">
                        Tutup
                    </flux:button>
                    @if(count($selectedBulkCompetitionIds) > 0)
                        <flux:button
                            icon="arrow-down-tray"
                            variant="subtle"
                            href="{{ $bulkDownloadUrl }}"
                            target="_blank"
                            title="Unduh langsung berkas PDF gabungan"
                        >
                            Unduh PDF
                        </flux:button>
                        <flux:button
                            icon="printer"
                            variant="primary"
                            href="{{ $bulkPrintUrl }}"
                            target="_blank"
                            title="Buka pratinjau PDF cetak massal"
                        >
                            Pratinjau PDF ({{ count($selectedBulkCompetitionIds) }} Lomba)
                        </flux:button>
                    @else
                        <flux:button
                            icon="printer"
                            variant="primary"
                            disabled
                        >
                            Pilih Lomba
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
