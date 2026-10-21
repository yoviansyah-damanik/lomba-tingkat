<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Breadcrumb & Title --}}
    <x-heading-section
        class="mb-6"
        title="Katalog Mata Lomba & Petunjuk Teknis (Juknis)"
        description="Daftar mata lomba resmi LT III Kwarcab Padangsidimpuan beserta aturan teknis dan skala penilaian."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Mata Lomba & Juknis</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-600 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                <span class="font-mono font-bold text-amber-700 dark:text-amber-400">{{ $this->competitions->count() }}</span>
                <span>Cabang Terdaftar</span>
            </div>
        </x-slot:actions>
    </x-heading-section>

    {{-- Notice Banner with Blind Scoring highlight --}}
    <div class="mb-8 rounded-xl border border-amber-300/60 bg-amber-50/60 p-4 text-xs text-amber-900 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-200 shadow-xs">
        <div class="flex items-start gap-2.5">
            <svg class="size-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="leading-relaxed">
                <strong>Penting untuk Bindamping & Peserta:</strong> Penilaian seluruh mata lomba mengadopsi sistem <em>Blind Scoring</em> di mana juri menilai nomor peserta/regu acak tanpa melihat identitas pangkalan/sekolah asal untuk menjamin objektivitas penuh.
            </div>
        </div>
    </div>

    {{-- Interactive Search and Filters (Livewire 4) --}}
    <div class="mb-8 flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 md:flex-row md:items-center md:justify-between">
        <div class="relative flex-1">
            <flux:icon name="magnifying-glass" class="absolute left-3.5 top-3 size-4 text-zinc-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari kode atau nama mata lomba..."
                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-10 pr-4 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            />
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-zinc-400 hover:text-zinc-600">
                    &times;
                </button>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select
                wire:model.live="formatFilter"
                class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                <option value="all">Semua Format Peserta</option>
                <option value="patrol">Regu Utuh (8 Peserta)</option>
                <option value="group">Kelompok (Interval)</option>
                <option value="individual">Individu (1 Peserta)</option>
            </select>

            <select
                wire:model.live="categoryFilter"
                class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                <option value="all">Semua Kategori</option>
                <option value="terampil">Keterampilan Kepramukaan</option>
                <option value="tangkas">Ketangkasan & Olahraga</option>
                <option value="cerdas">Pengetahuan & Wawasan</option>
                <option value="budaya">Seni Budaya & Keagamaan</option>
            </select>

            @if($search || $categoryFilter !== 'all' || $formatFilter !== 'all')
                <button
                    wire:click="$set('search', ''); $set('categoryFilter', 'all'); $set('formatFilter', 'all');"
                    class="rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    Atur Ulang
                </button>
            @endif
        </div>
    </div>

    {{-- Competitions List --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        @forelse($this->competitions as $competition)
            @php
                // Ringkasan ketentuan teknis: waktu & tempat diambil dari teks Juknis; bila tidak ada, potongan awal teks
                $guidelines = (string) $competition->guidelines_text;
                $time = preg_match('/^Waktu:\s*(.+)$/m', $guidelines, $m) ? $m[1] : null;
                $place = preg_match('/^Tempat:\s*(.+)$/m', $guidelines, $m) ? $m[1] : null;
                $fallbackSummary = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $guidelines)), 180);
            @endphp

            <article class="flex flex-col rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs transition hover:border-amber-400/70 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Kepala kartu --}}
                <header class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-mono text-sm font-bold text-amber-700 dark:text-amber-400">{{ $competition->code }}</span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">{{ $competition->categoryLabel() }}</span>
                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ $competition->participantRangeLabel() }}</span>
                    </div>
                </header>

                <h2 class="mt-4 text-lg font-bold leading-snug text-zinc-900 dark:text-zinc-100">{{ $competition->title }}</h2>
                <p class="mt-1.5 line-clamp-3 text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $competition->description }}</p>

                {{-- Ringkasan teknis --}}
                <dl class="mt-4 divide-y divide-zinc-100 rounded-xl bg-zinc-50 text-xs dark:divide-zinc-700/60 dark:bg-zinc-800/50">
                    @if($time || $place)
                        @if($time)
                            <div class="flex gap-3 px-3.5 py-2.5">
                                <dt class="w-14 shrink-0 font-semibold text-zinc-500 dark:text-zinc-400">Waktu</dt>
                                <dd class="text-zinc-800 dark:text-zinc-200">{{ $time }}</dd>
                            </div>
                        @endif
                        @if($place)
                            <div class="flex gap-3 px-3.5 py-2.5">
                                <dt class="w-14 shrink-0 font-semibold text-zinc-500 dark:text-zinc-400">Tempat</dt>
                                <dd class="text-zinc-800 dark:text-zinc-200">{{ $place }}</dd>
                            </div>
                        @endif
                    @else
                        <div class="px-3.5 py-2.5 leading-relaxed text-zinc-700 dark:text-zinc-300">
                            {{ $fallbackSummary ?: 'Ketentuan teknis detail tercantum dalam dokumen petunjuk teknis resmi panitia.' }}
                        </div>
                    @endif
                </dl>

                {{-- Sesi jadwal --}}
                @if($competition->schedules->count())
                    <div class="mt-5">
                        <h3 class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Sesi Jadwal</h3>
                        <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($competition->schedules as $sched)
                                @php
                                    $genderLabel = match ($sched->gender_category) {
                                        'boys' => ['Putra', 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300'],
                                        'girls' => ['Putri', 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'],
                                        default => ['Putra & Putri', 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'],
                                    };
                                @endphp
                                <li class="flex items-start justify-between gap-3 py-2.5 text-xs">
                                    <div class="min-w-0">
                                        <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-bold {{ $genderLabel[1] }}">{{ $genderLabel[0] }}</span>
                                        <p class="mt-1 truncate text-zinc-600 dark:text-zinc-400" title="{{ $sched->arena_location }}">{{ $sched->arena_location }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $sched->start_time->translatedFormat('D, d M Y') }}</p>
                                        <p class="font-mono text-[11px] text-amber-700 dark:text-amber-400">
                                            {{ $sched->start_time->format('H:i') }}@if($sched->end_time)&ndash;{{ $sched->end_time->format('H:i') }}@endif WIB
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Aksi --}}
                <footer class="mt-auto flex items-center justify-between gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    <span class="text-xs text-zinc-400">Nilai: {{ $competition->scoring_formula === 'sum' ? 'Akumulasi juri' : 'Rata-rata juri' }}</span>
                    <div class="flex items-center gap-1">
                        <flux:button size="sm" variant="primary" icon-trailing="arrow-right" wire:click="openDetails('{{ $competition->id }}')">
                            Juknis & Rubrik
                        </flux:button>
                        <flux:button size="sm" variant="subtle" href="{{ route('schedules') }}" wire:navigate>
                            Jadwal
                        </flux:button>
                    </div>
                </footer>
            </article>
        @empty
            <div class="col-span-2 rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-sm text-zinc-500 dark:border-zinc-800">
                Tidak ada mata lomba yang sesuai dengan filter pencarian.
            </div>
        @endforelse
    </div>

    {{-- Interactive Guidelines & Rubric Modal --}}
    @if($showDetailsModal && $this->selectedCompetition)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
            <div class="relative w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 max-h-[90vh] overflow-y-auto">
                <button wire:click="closeDetails" class="absolute right-4 top-4 rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800">
                    <flux:icon name="x-mark" class="size-5" />
                </button>

                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-amber-700 dark:text-amber-400">{{ $this->selectedCompetition->code }}</span>
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        {{ $this->selectedCompetition->categoryLabel() }}
                    </span>
                    <span class="rounded border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $this->selectedCompetition->participantRangeLabel() }}
                    </span>
                </div>

                <h3 class="mt-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->selectedCompetition->title }}
                </h3>

                <p class="mt-2 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    {{ $this->selectedCompetition->description }}
                </p>

                <div class="mt-4 rounded-xl bg-amber-50/70 p-3.5 text-xs text-amber-900 dark:bg-amber-950/30 dark:text-amber-200 border border-amber-200/50 dark:border-amber-500/20">
                    <span class="block font-semibold mb-1">Ketentuan Teknis Resmi (Juknis):</span>
                    <p class="leading-relaxed whitespace-pre-line">{{ $this->selectedCompetition->guidelines_text ?? 'Ketentuan teknis lengkap tersedia pada dokumen fisik Juknis Panitia.' }}</p>
                </div>

                {{-- Criteria Rubric --}}
                <div class="mt-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-2">
                        Rubrik Kriteria Penilaian (Blind Scoring)
                    </h4>
                    @if($this->selectedCompetition->criteria->count())
                        <div class="space-y-2">
                            @foreach($this->selectedCompetition->criteria as $criterion)
                                <div class="flex items-start justify-between rounded-xl border border-zinc-100 bg-zinc-50/50 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-800/40">
                                    <div>
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $criterion->name }}</div>
                                        <div class="text-[11px] text-zinc-500">{{ $criterion->description ?: 'Standar penilaian juri' }}</div>
                                    </div>
                                    <div class="text-right shrink-0 ml-3">
                                        <span class="font-mono font-bold text-amber-700 dark:text-amber-400">{{ $criterion->min_score }} - {{ $criterion->max_score }}</span>
                                        <div class="text-[10px] text-zinc-400">Bobot: {{ $criterion->weight_percentage }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-zinc-400 italic">Rubrik penilaian detail akan ditentukan saat briefing technical meeting dewan juri.</p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <button wire:click="closeDetails" class="rounded-xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
