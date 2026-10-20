<x-layouts::public title="Mata Lomba & Juknis Kegiatan">
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
        </x-heading-section>

        {{-- Notice Banner --}}
        <div class="mb-8 rounded-xl border border-amber-300/60 bg-amber-50/60 p-4 text-xs text-amber-900 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-200">
            <div class="flex items-start gap-2.5">
                <svg class="size-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="leading-relaxed">
                    <strong>Penting untuk Bindamping & Peserta:</strong> Penilaian seluruh mata lomba mengadopsi sistem <em>Blind Scoring</em> di mana juri menilai nomor peserta/regu acak tanpa melihat identitas pangkalan/sekolah asal untuk menjamin objektivitas penuh.
                </div>
            </div>
        </div>

        {{-- Competitions List --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @forelse($competitions as $competition)
                <div class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div>
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                            <span class="font-mono text-sm font-bold text-amber-700 dark:text-amber-400">
                                {{ $competition->code }}
                            </span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                    {{ $competition->categoryLabel() }}
                                </span>
                                <span class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                    {{ $competition->participantRangeLabel() }}
                                </span>
                            </div>
                        </div>

                        <h2 class="mt-4 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $competition->title }}
                        </h2>

                        <p class="mt-2 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            {{ $competition->description }}
                        </p>

                        {{-- Technical Guidelines Summary --}}
                        <div class="mt-4 rounded-xl bg-zinc-50 p-3.5 text-xs text-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300">
                            <span class="block font-semibold text-zinc-900 dark:text-zinc-100 mb-1">
                                Ketentuan Teknis Lomba:
                            </span>
                            <p class="leading-relaxed">
                                {{ $competition->guidelines_text ?? 'Ketentuan teknis detail tercantum dalam dokumen petunjuk teknis juknis resmi panitia.' }}
                            </p>
                        </div>

                        {{-- Scheduled Sessions --}}
                        @if($competition->schedules->count())
                            <div class="mt-4">
                                <span class="block text-[11px] font-semibold text-zinc-500 uppercase tracking-wider mb-1.5">
                                    Sesi Jadwal Terdaftar:
                                </span>
                                <div class="space-y-1.5">
                                    @foreach($competition->schedules as $sched)
                                        <div class="flex items-center justify-between rounded-lg border border-zinc-200/60 px-3 py-1.5 text-xs text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $sched->session_name }} ({{ $sched->arena_location }})</span>
                                                <span class="inline-flex items-center rounded px-1.5 py-0.2 text-[10px] font-medium {{ $sched->participantTypeBadgeClasses() }}">
                                                    {{ $sched->isIndividual() ? 'Individu' : 'Regu' }}
                                                </span>
                                            </div>
                                            <span class="font-mono text-[11px] text-amber-700 dark:text-amber-400">
                                                {{ $sched->start_time->translatedFormat('d M, H:i') }} WIB
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs">
                        <span class="text-zinc-400">Formula Nilai: {{ ucfirst($competition->scoring_formula) }}</span>
                        <a href="{{ route('schedules') }}" wire:navigate class="font-semibold text-amber-700 hover:underline dark:text-amber-400">
                            Lihat Jadwal &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-sm text-zinc-500 dark:border-zinc-800">
                    Belum ada mata lomba yang dipublikasikan.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts::public>
