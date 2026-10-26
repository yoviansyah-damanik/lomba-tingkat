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

    {{-- Modal Juknis & Rubrik --}}
    <flux:modal wire:model.self="showDetailsModal" class="w-full max-w-3xl !p-0 overflow-hidden" x-on:close="$wire.closeDetails()">
        @if($this->selectedCompetition)
            @php
                $c = $this->selectedCompetition;

                // Uraikan teks Juknis ("Label: isi" per blok) menjadi bagian-bagian terstruktur
                $parts = [];
                foreach (preg_split("/\n{2,}/", trim((string) $c->guidelines_text)) as $block) {
                    if (preg_match('/^([^:\n]{2,40}):[ \t]*(.*)$/su', trim($block), $m)) {
                        $parts[trim($m[1])] = trim($m[2]);
                    }
                }
                $steps = collect(preg_split("/\n/", $parts['Tahap & Prosedur'] ?? ''))
                    ->map(fn ($line) => trim(preg_replace('/^[a-z]\.\s*/u', '', $line)))
                    ->filter()->values();
                $facts = [
                    ['Waktu', 'clock', $parts['Waktu'] ?? null],
                    ['Tempat', 'map-pin', $parts['Tempat'] ?? null],
                    ['Peserta', 'user-group', $parts['Peserta'] ?? null],
                    ['Pakaian', 'identification', $parts['Pakaian'] ?? null],
                ];
                $gear = [
                    ['Perlengkapan Peserta', 'backpack', $parts['Perlengkapan peserta'] ?? null],
                ];
                $totalWeight = (float) $c->criteria->sum('weight_percentage');
                $sessions = $c->schedules->where('is_published', true)->sortBy('start_time');
            @endphp

            {{-- Header --}}
            <div class="relative overflow-hidden bg-linear-to-br from-amber-600 via-amber-500 to-amber-400 px-6 pb-6 pt-7 text-white dark:from-amber-800 dark:via-amber-700 dark:to-amber-600">
                <div class="pointer-events-none absolute -right-10 -top-10 size-44 rounded-full bg-white/20 blur-2xl"></div>
                <div class="relative">
                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold">
                        <span class="rounded-full bg-zinc-950/80 px-2.5 py-1 font-mono tracking-wider">{{ $c->code }}</span>
                        <span class="rounded-full bg-white/25 px-2.5 py-1 backdrop-blur-sm">{{ $c->categoryLabel() }}</span>
                        <span class="rounded-full bg-white/25 px-2.5 py-1 backdrop-blur-sm">{{ $c->participantRangeLabel() }}</span>
                    </div>
                    <h3 class="mt-3 text-2xl font-black leading-tight sm:text-3xl">{{ $c->title }}</h3>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-white/90 sm:text-sm">{{ $c->description }}</p>
                </div>
            </div>

            <div class="max-h-[62vh] space-y-7 overflow-y-auto px-6 py-6">
                {{-- Tujuan --}}
                @if(! empty($parts['Tujuan']))
                    <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-950/30">
                        <flux:icon name="flag" class="mt-0.5 size-5 shrink-0 text-amber-600" />
                        <div class="text-xs">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Tujuan Lomba</span>
                            <p class="mt-0.5 leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $parts['Tujuan'] }}</p>
                        </div>
                    </div>
                @endif

                {{-- Fakta ringkas --}}
                @if(collect($facts)->contains(fn ($f) => $f[2]))
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($facts as [$label, $icon, $value])
                            @if($value)
                                <div class="flex items-start gap-3 rounded-2xl border border-zinc-200 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                        <flux:icon :name="$icon" class="size-5" />
                                    </span>
                                    <div class="min-w-0 text-xs">
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $label }}</span>
                                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $value }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Tahap & prosedur --}}
                @if($steps->isNotEmpty())
                    <section>
                        <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500">
                            <flux:icon name="list-bullet" class="size-4 text-amber-600" /> Tahap &amp; Prosedur
                        </h4>
                        <ol class="mt-3 space-y-0">
                            @foreach($steps as $i => $step)
                                <li class="relative flex gap-3 pb-4 last:pb-0">
                                    @if(! $loop->last)
                                        <span class="absolute left-3 top-7 h-[calc(100%-1.75rem)] w-px bg-amber-200 dark:bg-amber-900/60"></span>
                                    @endif
                                    <span class="relative z-10 flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-500 text-[11px] font-bold text-white shadow-xs">{{ $i + 1 }}</span>
                                    <p class="pt-0.5 text-xs leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $step }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                {{-- Rubrik penilaian --}}
                <section>
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500">
                            <flux:icon name="clipboard-document-check" class="size-4 text-amber-600" /> Rubrik Penilaian (Blind Scoring)
                        </h4>
                        @if($c->criteria->count())
                            <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $c->criteria->count() }} aspek &bull; {{ rtrim(rtrim(number_format($totalWeight, 2, ',', '.'), '0'), ',') }}%</span>
                        @endif
                    </div>

                    @if($c->criteria->count())
                        <div class="mt-3 space-y-2.5">
                            @foreach($c->criteria as $criterion)
                                <div class="rounded-2xl border border-zinc-200 bg-zinc-50/60 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/40">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 text-xs">
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $criterion->name }}</div>
                                            @if($criterion->description)
                                                <div class="mt-0.5 text-[11px] text-zinc-500">{{ $criterion->description }}</div>
                                            @endif
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <span class="text-lg font-black text-amber-700 dark:text-amber-400">{{ rtrim(rtrim(number_format((float) $criterion->weight_percentage, 2, ',', '.'), '0'), ',') }}%</span>
                                            <div class="text-[10px] text-zinc-400">skor {{ (int) $criterion->min_score }}&ndash;{{ (int) $criterion->max_score }}</div>
                                        </div>
                                    </div>
                                    <progress value="{{ (float) $criterion->weight_percentage }}" max="{{ max($totalWeight, 1) }}" class="mt-2 h-1.5 w-full overflow-hidden rounded-full [&::-moz-progress-bar]:bg-amber-500 [&::-webkit-progress-bar]:bg-amber-100 [&::-webkit-progress-value]:bg-amber-500 dark:[&::-webkit-progress-bar]:bg-zinc-700"></progress>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 rounded-xl border border-dashed border-zinc-300 p-4 text-center text-xs italic text-zinc-400 dark:border-zinc-700">
                            Rubrik penilaian detail akan ditentukan saat technical meeting dewan juri.
                        </p>
                    @endif
                </section>

                {{-- Perlengkapan --}}
                @if(collect($gear)->contains(fn ($g) => $g[2]))
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($gear as [$label, $icon, $value])
                            @if($value)
                                <div class="rounded-2xl border border-zinc-200 bg-white p-3.5 text-xs dark:border-zinc-800 dark:bg-zinc-900">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $label }}</span>
                                    <p class="mt-1 leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $value }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Keterangan tambahan --}}
                @if(! empty($parts['Keterangan']))
                    <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50/70 p-4 text-xs dark:border-blue-900/50 dark:bg-blue-950/30">
                        <flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0 text-blue-600" />
                        <p class="leading-relaxed text-blue-950 dark:text-blue-200">{{ $parts['Keterangan'] }}</p>
                    </div>
                @endif

                {{-- Teks mentah bila Juknis tidak memakai format baku --}}
                @if(empty($parts) && filled($c->guidelines_text))
                    <p class="whitespace-pre-line rounded-2xl bg-zinc-50 p-4 text-xs leading-relaxed text-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-300">{{ $c->guidelines_text }}</p>
                @endif

                {{-- Sesi terjadwal --}}
                @if($sessions->isNotEmpty())
                    <section>
                        <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500">
                            <flux:icon name="calendar-days" class="size-4 text-amber-600" /> Sesi Terjadwal
                        </h4>
                        <ul class="mt-3 space-y-2">
                            @foreach($sessions as $session)
                                <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-zinc-200 px-3.5 py-2.5 text-xs dark:border-zinc-800">
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $session->session_name }}</span>
                                    <span class="text-zinc-500">
                                        {{ $session->start_time?->timezone('Asia/Jakarta')->translatedFormat('D, d M Y') }}
                                        &bull; {{ $session->start_time?->timezone('Asia/Jakarta')->format('H:i') }}&ndash;{{ $session->end_time?->timezone('Asia/Jakarta')->format('H:i') }} WIB
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                <span class="text-[11px] text-zinc-500">Nilai: {{ $c->scoring_formula === 'sum' ? 'Akumulasi juri' : 'Rata-rata juri' }}</span>
                <div class="flex items-center gap-2">
                    <flux:button size="sm" variant="subtle" icon="calendar-days" href="{{ route('schedules') }}" wire:navigate>Lihat Jadwal</flux:button>
                    <flux:button size="sm" variant="primary" icon="check" wire:click="closeDetails">Tutup</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
