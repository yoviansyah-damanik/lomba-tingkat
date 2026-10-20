<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-heading-section
        title="Penilaian Regu"
        description="Nilai dan pencapaian regu pada setiap mata lomba. Hanya nilai yang sudah difinalisasi juri yang ditampilkan."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dasbor</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Penilaian Regu</span>
        </x-slot:breadcrumbs>
    </x-heading-section>

    @if (! $patrol)
        <div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-sm text-zinc-500 dark:border-zinc-800">
            Akun Anda belum ditautkan ke regu. Hubungi Administrator untuk menautkan data regu.
        </div>
    @else
        {{-- Pilihan regu (bindamping dengan lebih dari satu regu) --}}
        @if ($patrols->count() > 1)
            <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                <div class="flex w-max items-center gap-2">
                    @foreach ($patrols as $option)
                        <flux:button
                            size="sm"
                            variant="{{ $option->id === $patrol->id ? 'primary' : 'subtle' }}"
                            wire:click="$set('patrolId', '{{ $option->id }}')"
                            wire:key="patrol-{{ $option->id }}"
                            class="shrink-0"
                        >
                            {{ $option->patrol_name }} &bull; {{ $option->isBoys() ? 'Putra' : 'Putri' }}
                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Identitas regu --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $patrol->isBoys() ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300' }}">
                    {{ $patrol->isBoys() ? 'Penggalang Putra' : 'Penggalang Putri' }}
                </span>
                <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $patrol->participantCount() }} peserta</span>
            </div>
            <h2 class="mt-2 text-xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ $patrol->patrol_name }}</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $patrol->scoutGroup?->school_name }}
                @if ($patrol->group_number)
                    <span class="text-zinc-400">&bull; Gudep {{ $patrol->group_number }}</span>
                @endif
            </p>
        </div>

        {{-- Ringkasan pencapaian --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat-card
                label="Peringkat Umum"
                :value="$standing ? '#'.$standing['rank'] : '-'"
                :description="$standing && $totalInCategory ? 'dari '.$totalInCategory.' regu '.($patrol->isBoys() ? 'putra' : 'putri') : 'Belum ada nilai'"
                icon="trophy"
                color="amber"
            />
            <x-stat-card
                label="Total Prestasi"
                :value="$standing['total_medals'] ?? 0"
                unit="prestasi"
                description="Peringkat ditentukan jumlah Tinggi, lalu Baik, lalu Cukup"
                icon="star"
                color="indigo"
            />
            <x-stat-card
                label="Jumlah Predikat"
                :value="($standing['gold'] ?? 0).' / '.($standing['silver'] ?? 0).' / '.($standing['bronze'] ?? 0)"
                description="Tinggi / Baik / Cukup"
                icon="academic-cap"
                color="emerald"
            />
            <x-stat-card
                label="Lomba Dinilai"
                :value="$scores->count()"
                unit="lomba"
                :description="'Akumulasi nilai '.number_format($standing['total_score_sum'] ?? 0, 2)"
                icon="clipboard-document-check"
                color="blue"
            />
        </div>

        @if ($standing && $standing['general_rank_title'])
            <div class="flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm font-semibold text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/30 dark:text-amber-200">
                <flux:icon name="trophy" class="size-5 shrink-0 text-amber-600 dark:text-amber-400" />
                <span>{{ $standing['general_rank_title'] }}</span>
            </div>
        @endif

        {{-- Nilai per mata lomba --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Nilai per Mata Lomba</h3>

            @forelse ($scores as $score)
                <article wire:key="score-{{ $score->id }}" class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-xl bg-amber-500/10 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                        <span class="text-[9px] font-semibold uppercase leading-none">Rank</span>
                        <span class="text-lg font-extrabold leading-tight">{{ $score->rank ?? '-' }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="font-mono text-[11px] font-bold text-amber-700 dark:text-amber-400">{{ $score->competition?->code }}</p>
                        <h4 class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $score->competition?->title }}</h4>
                        <span class="mt-1 inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $score->predicateBadgeClasses() }}">
                            {{ $score->predicateLabel() }}
                        </span>
                    </div>

                    <div class="text-right">
                        <p class="font-mono text-xl font-extrabold text-zinc-900 dark:text-white">{{ number_format((float) $score->final_score, 2) }}</p>
                        <p class="text-[10px] text-zinc-400">nilai akhir</p>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 p-10 text-center text-sm text-zinc-500 dark:border-zinc-800">
                    Belum ada nilai yang difinalisasi untuk regu ini.
                </div>
            @endforelse
        </section>
    @endif
</div>
