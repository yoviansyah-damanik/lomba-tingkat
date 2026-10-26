<div class="space-y-6">
    {{-- Header Section & Tombol Kembali --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button
                icon="arrow-left"
                variant="subtle"
                href="{{ route('judge.index') }}"
                wire:navigate
            >
                Kembali ke Cockpit
            </flux:button>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $competition->title }}
                </h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Arena: <strong class="text-zinc-700 dark:text-zinc-300">{{ $schedule->arena_location }}</strong> &bull; Sesi {{ $schedule->session_name }} &bull; Kategori: Regu {{ $schedule->gender_category === 'boys' ? 'Putra' : 'Putri' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($isAdmin)
                <flux:button
                    icon="{{ $unmaskNames ? 'eye-slash' : 'eye' }}"
                    variant="{{ $unmaskNames ? 'filled' : 'subtle' }}"
                    wire:click="toggleUnmask"
                >
                    {{ $unmaskNames ? 'Sembunyikan Identitas' : 'Buka Identitas (Admin)' }}
                </flux:button>
            @endif

            <flux:button
                icon="book-open"
                variant="subtle"
                href="{{ route('competitions') }}"
                target="_blank"
            >
                Juknis Rubrik
            </flux:button>
        </div>
    </div>

    {{-- Banner Protokol Penjurian Berbasis NPP --}}
    <div class="rounded-2xl border border-indigo-200/80 bg-gradient-to-r from-indigo-50/70 via-blue-50/40 to-white dark:border-indigo-900/40 dark:from-indigo-950/30 dark:via-zinc-900 dark:to-zinc-900 p-4 shadow-xs">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                    <flux:icon name="shield-check" class="size-4" />
                </span>
                <div>
                    <h4 class="text-xs font-bold text-indigo-950 dark:text-indigo-200">
                        PENJURIAN BERBASIS NPP (NOMOR PENDAFTARAN PESERTA)
                    </h4>
                    <p class="text-[11px] text-indigo-900/80 dark:text-indigo-300/80">
                        Masukkan NPP regu untuk membuka form penilaian. Nama regu, nomor gugus depan, dan pangkalan tidak ditampilkan kepada juri.
                    </p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs font-medium text-zinc-500">
                <span>Total Kriteria: <strong>{{ $criteria->count() }}</strong></span>
                <span>&bull;</span>
                <span>Bobot Total: <strong>{{ $criteria->sum('weight_percentage') }}%</strong></span>
            </div>
        </div>
    </div>

    @if($isAdmin)
        <div class="rounded-2xl border border-amber-300 bg-amber-50/70 dark:border-amber-900/40 dark:bg-amber-950/20 p-4 shadow-xs flex items-center gap-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white">
                <flux:icon name="information-circle" class="size-4" />
            </span>
            <div>
                <h4 class="text-xs font-bold text-amber-950 dark:text-amber-200">
                    MODE TINJAU AUDIT ADMINISTRATOR (READ-ONLY)
                </h4>
                <p class="text-[11px] text-amber-900/80 dark:text-amber-300/80">
                    Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk. Administrator tidak diperkenankan melakukan penilaian, namun berhak meninjau, membuka identitas peserta (unmask), dan membuka kunci nilai untuk revisi resmi bila diperlukan.
                </p>
            </div>
        </div>
    @endif

    {{-- Layout 2 Kolom: Input NPP & Form Rubrik --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        {{-- Kolom Kiri: Input NPP & riwayat penilaian sesi --}}
        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                        Langkah 2 &mdash; Masukkan NPP
                    </h3>
                    <p class="mt-1 text-[11px] text-zinc-400">
                        Contoh: 01 atau 12. NPP dicari pada kategori sesi ini (putra atau putri). Sistem memverifikasi apakah NPP termasuk peserta lomba ini.
                    </p>
                </div>

                @if(! $nppReady)
                    <div class="flex items-start gap-2.5 rounded-xl border border-amber-300 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
                        <flux:icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0 text-amber-600" />
                        <span class="font-medium">NPP belum digenerate panitia. Penilaian dapat dimulai setelah pendaftaran ditutup dan NPP dibuat.</span>
                    </div>
                @endif

                <form wire:submit="submitNpp" class="space-y-3">
                    <flux:input
                        wire:model="nppInput"
                        placeholder="01"
                        autocomplete="off"
                        class="font-mono uppercase"
                        :invalid="(bool) $nppError"
                    />

                    @if($nppError)
                        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">
                            <flux:icon name="x-circle" class="mt-0.5 size-4 shrink-0" />
                            <span>{{ $nppError }}</span>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <flux:button type="submit" variant="primary" icon="magnifying-glass" :disabled="! $nppReady">
                            Verifikasi NPP
                        </flux:button>
                        @if($selectedPatrol)
                            <flux:button type="button" variant="subtle" icon="arrow-path" wire:click="resetNpp">
                                NPP Lain
                            </flux:button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="space-y-2">
                <div class="px-1 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                        {{ $isAdmin ? 'Penilaian Masuk' : 'Sudah Anda Nilai' }} ({{ $scoredList->count() }})
                    </h3>
                    <span class="text-[11px] text-zinc-400">Klik untuk membuka</span>
                </div>

                <div class="space-y-2 max-h-[520px] overflow-y-auto pr-1">
                    @forelse($scoredList as $item)
                        <button
                            type="button"
                            wire:click="selectPatrol('{{ $item['patrol_id'] }}')"
                            class="w-full text-left rounded-2xl border p-3 transition shadow-xs {{ $selectedPatrolId === $item['patrol_id'] ? 'border-amber-500 bg-amber-50/60 ring-2 ring-amber-500/20 dark:border-amber-500 dark:bg-amber-950/30' : 'border-zinc-200/80 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700' }}"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="rounded-lg bg-zinc-100 px-2 py-0.5 font-mono text-xs font-extrabold text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">
                                    {{ $item['npp'] }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($item['final_score'], 2) }}</span>
                                    @if($item['status'] === 'finalized')
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            <flux:icon name="lock-closed" class="size-3" /> Final
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            <flux:icon name="pencil" class="size-3" /> Draf
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="rounded-2xl border border-zinc-200 bg-white p-5 text-center text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
                            Belum ada NPP yang dinilai pada sesi ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Lembar Penilaian Rubrik (Formulir Penilaian Aktif) --}}
        <div class="lg:col-span-8 space-y-4">
            @if($selectedPatrol)
                {{-- Kartu Info Peserta Aktif --}}
                <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-amber-600 px-2.5 py-1 font-mono text-sm font-extrabold text-white">
                                    {{ $selectedPatrol['npp'] }}
                                </span>
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                    NPP {{ $selectedPatrol['npp'] }}
                                </h2>
                                <span class="rounded-md {{ $selectedPatrol['gender_category'] === 'boys' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }} px-2 py-0.5 text-xs font-semibold">
                                    Kategori {{ $selectedPatrol['gender_label'] }}
                                </span>
                            </div>

                            @if($unmaskNames && $selectedPatrol['patrol_name'])
                                <p class="mt-1 text-xs text-amber-700 dark:text-amber-400 font-medium">
                                    Identitas Resmi: <strong>{{ $selectedPatrol['patrol_name'] }}</strong> &bull; {{ $selectedPatrol['school_name'] }}
                                </p>
                            @endif
                        </div>

                        {{-- Indikator Status & Kunci --}}
                        <div class="flex items-center gap-2">
                            @if($activeAssessment && $activeAssessment->isLocked())
                                <div class="flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <flux:icon name="lock-closed" class="size-4" />
                                    <span>Telah Difinalisasi (Terkunci)</span>
                                </div>
                                @if($isAdmin)
                                    <flux:button
                                        icon="lock-open"
                                        variant="subtle"
                                        wire:click="openUnlockModal"
                                    >
                                        Buka Kunci
                                    </flux:button>
                                @endif
                            @else
                                <span class="rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    Status: <strong>Draf Terbuka</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Formulir Rubrik Penilaian Kriteria --}}
                <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 sm:p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                    <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                            Rubrik Evaluasi Dewan Juri
                        </h3>
                        <span class="text-xs text-zinc-400">
                            Semua nilai dihitung otomatis sesuai bobot
                        </span>
                    </div>

                    @php
                        $scoringClosedMessage = $this->scoringClosedMessage;
                        $canScore = $isAssignedJudge && ! $scoringClosedMessage && (! $activeAssessment || ! $activeAssessment->isLocked());
                    @endphp

                    @if($scoringClosedMessage)
                        <div class="flex items-start gap-2.5 rounded-xl border border-amber-300 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
                            <flux:icon name="clock" class="mt-0.5 size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                            <span class="font-medium">{{ $scoringClosedMessage }}</span>
                        </div>
                    @endif

                    <div class="space-y-4">
                        @forelse($criteria as $criterion)
                            @php
                                $cScore = (float) ($criteriaScores[$criterion->id] ?? $criterion->min_score);
                                $totalW = $criteria->sum('weight_percentage') ?: 100;
                                $cWeighted = $cScore * ((float) $criterion->weight_percentage / $totalW);
                            @endphp
                            <div class="rounded-xl border border-zinc-200/70 p-4 transition dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/40 dark:bg-zinc-800/20">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            @if($criterion->code)
                                                <span class="rounded bg-zinc-200 px-1.5 py-0.5 font-mono text-[11px] font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                                                    {{ $criterion->code }}
                                                </span>
                                            @endif
                                            <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                                {{ $criterion->name }}
                                            </h4>
                                        </div>
                                        @if($criterion->description)
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                                                {{ $criterion->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                                            Bobot: {{ (float) $criterion->weight_percentage }}%
                                        </span>
                                        <span class="text-xs text-zinc-400">
                                            Rentang: {{ (float) $criterion->min_score }} - {{ (float) $criterion->max_score }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Input Nilai & Slider Interaktif --}}
                                <div class="mt-3.5 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                    <div class="sm:col-span-8 flex items-center gap-3">
                                        <input
                                            type="range"
                                            wire:model.live.debounce.250ms="criteriaScores.{{ $criterion->id }}"
                                            min="{{ (float) $criterion->min_score }}"
                                            max="{{ (float) $criterion->max_score }}"
                                            step="0.5"
                                            @disabled(! $canScore)
                                            class="w-full accent-amber-600 dark:accent-amber-500 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
                                        />
                                    </div>

                                    <div class="sm:col-span-4 flex items-center justify-between sm:justify-end gap-3">
                                        <div class="w-24">
                                            <input
                                                type="number"
                                                wire:model.live.debounce.300ms="criteriaScores.{{ $criterion->id }}"
                                                min="{{ (float) $criterion->min_score }}"
                                                max="{{ (float) $criterion->max_score }}"
                                                step="0.1"
                                                @disabled(! $canScore)
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-2.5 py-1.5 text-center font-mono text-sm font-extrabold text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 disabled:bg-zinc-100 dark:disabled:bg-zinc-800"
                                            />
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[11px] text-zinc-400 block">Terbobot</span>
                                            <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">
                                                {{ number_format($cWeighted, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-xs text-zinc-500 dark:border-zinc-700">
                                Belum ada kriteria penilaian yang ditetapkan untuk cabang lomba ini.
                            </div>
                        @endforelse
                    </div>

                    {{-- Catatan Khusus Dewan Juri --}}
                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Catatan & Masukan Evaluasi Juri (Opsional):
                        </label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            placeholder="Tuliskan catatan teknis atau apresiasi penilaian untuk regu ini..."
                            @disabled(! $canScore)
                            class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 disabled:bg-zinc-100 dark:disabled:bg-zinc-800"
                        ></textarea>
                    </div>

                    {{-- Ringkasan Akumulasi Nilai Terhitung --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">
                                    Total Nilai Terbobot Akhir:
                                </span>
                                <p class="text-[11px] text-amber-700/80 dark:text-amber-400/80">
                                    Berdasarkan persentase bobot kriteria rubrik
                                </p>
                            </div>

                            <div class="flex items-baseline gap-2">
                                <span class="text-xs text-zinc-500">Skor:</span>
                                <span class="font-mono text-3xl font-extrabold text-amber-900 dark:text-amber-100">
                                    {{ number_format($calculatedScore['final_score'], 2) }}
                                </span>
                                <span class="text-xs text-zinc-400">
                                    (Mentah: {{ number_format($calculatedScore['raw_total'], 2) }})
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Bilah Tombol Aksi --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <div>
                            @if($activeAssessment && $activeAssessment->finalized_at)
                                <span class="text-xs text-zinc-500">
                                    Difinalisasi pada: <strong>{{ $activeAssessment->finalized_at->format('d/m/Y H:i') }} WIB</strong>
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($isAssignedJudge)
                                @if(! $activeAssessment || ! $activeAssessment->isLocked())
                                    <flux:button
                                        icon="document-text"
                                        variant="subtle"
                                        wire:click="saveDraft"
                                        :disabled="(bool) $scoringClosedMessage"
                                    >
                                        Simpan Draf
                                    </flux:button>

                                    <flux:button
                                        icon="lock-closed"
                                        variant="primary"
                                        wire:click="openFinalizeModal"
                                        :disabled="(bool) $scoringClosedMessage"
                                    >
                                        Kunci & Finalisasi Nilai
                                    </flux:button>
                                @else
                                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                                        <flux:icon name="check-badge" class="size-4" />
                                        Nilai telah Anda finalisasi dan dikunci
                                    </span>
                                @endif
                            @elseif($isAdmin)
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-amber-800 dark:text-amber-300 bg-amber-100/80 dark:bg-amber-950/60 px-2.5 py-1 rounded-lg flex items-center gap-1.5">
                                        <flux:icon name="eye" class="size-3.5 text-amber-600" />
                                        Mode Tinjau Administrator
                                    </span>
                                    @if($activeAssessment && $activeAssessment->isLocked())
                                        <flux:button
                                            icon="lock-open"
                                            variant="subtle"
                                            wire:click="openUnlockModal"
                                        >
                                            Buka Kunci Nilai
                                        </flux:button>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs font-medium text-rose-600 dark:text-rose-400">
                                    Anda bukan juri yang ditugaskan pada sesi lomba ini.
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-zinc-200 bg-white p-12 text-center shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <flux:icon name="user-group" class="mx-auto size-12 text-zinc-400" />
                    <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        Masukkan NPP untuk Memulai Penilaian
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500">
                        Ketik NPP regu pada kolom di sebelah kiri. Jika NPP valid, form penilaian rubrik akan tampil; jika tidak, sistem menampilkan notifikasi.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Konfirmasi Finalisasi Nilai --}}
    @if($isFinalizeModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400">
                        <flux:icon name="lock-closed" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                            Konfirmasi Finalisasi Nilai Resmi
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 leading-relaxed">
                            Apakah Anda yakin ingin memfinalisasi nilai untuk <strong>NPP {{ $selectedPatrol['npp'] ?? '-' }}</strong> dengan skor akhir <strong>{{ number_format($calculatedScore['final_score'], 2) }}</strong>?
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-300">
                    <strong>Peringatan Integritas:</strong> Nilai yang difinalisasi akan langsung dikunci dan diakumulasikan ke klasemen prestasi resmi. Anda tidak dapat mengubahnya kembali tanpa persetujuan tertulis Administrator.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <flux:button
                        variant="subtle"
                        wire:click="$set('isFinalizeModalOpen', false)"
                    >
                        Batal
                    </flux:button>
                    <flux:button
                        variant="primary"
                        icon="check"
                        wire:click="finalize"
                    >
                        Ya, Kunci & Finalisasi
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Pembukaan Kunci Nilai (Khusus Administrator) --}}
    @if($isUnlockModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400">
                        <flux:icon name="lock-open" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                            Buka Kunci Penilaian (Audit Admin)
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 leading-relaxed">
                            Membuka kunci penilaian untuk <strong>NPP {{ $selectedPatrol['npp'] ?? '-' }}</strong> agar juri dapat melakukan perbaikan. Aksi ini akan dicatat dalam riwayat <em>Audit Trail</em>.
                        </p>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Alasan Pembukaan Kunci / Koreksi Nilai (Wajib):
                    </label>
                    <textarea
                        wire:model="unlockReason"
                        rows="3"
                        placeholder="Contoh: Kesalahan input kriteria PBB nomor 3 sesuai pengaduan juri pos..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    ></textarea>
                    @error('unlockReason')
                        <span class="text-[11px] text-rose-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <flux:button
                        variant="subtle"
                        wire:click="$set('isUnlockModalOpen', false)"
                    >
                        Batal
                    </flux:button>
                    <flux:button
                        variant="danger"
                        icon="lock-open"
                        wire:click="unlock"
                    >
                        Buka Kunci Sekarang
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
