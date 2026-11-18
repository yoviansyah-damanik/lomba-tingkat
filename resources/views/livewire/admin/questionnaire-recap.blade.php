<div class="space-y-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-400/10 dark:text-amber-400">
                    Dashboard Evaluasi Sistem
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $event?->name ?: 'LT-III Kwarcab Padangsidimpuan' }}
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-zinc-900 sm:text-3xl dark:text-zinc-100 flex items-center gap-2.5">
                <flux:icon name="chart-bar-square" class="size-8 text-amber-500" />
                <span>Rekapitulasi Kuisioner Panitia &amp; Dewan Juri</span>
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Analisis data kepuasan, performa operasional lapangan, dan aspirasi pengembangan fitur dari aparatur kegiatan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button href="{{ route('questionnaire.index') }}" target="_blank" icon="eye" variant="outline">
                Buka Form Kuisioner
            </flux:button>
        </div>
    </div>

    {{-- Ringkasan Metrik KPI --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Total Responden" :value="$stats['total']" unit="Orang"
            :description="$stats['committee'].' panitia • '.$stats['judge'].' juri'" color="amber" icon="users" />

        <x-stat-card label="Rata-rata Rating Keseluruhan" :value="$stats['avg_combined'].' / 5.0'" unit="Bintang"
            description="Skor kepuasan gabungan seluruh aspek" color="emerald" icon="star" />

        <x-stat-card label="Indeks Efisiensi Positif" :value="$stats['positive_efficiency_rate'].' %'" unit="Responden"
            description="Merasa jauh lebih efisien & sangat membantu" color="indigo" icon="bolt" />

        <x-stat-card label="Partisipasi Dewan Juri" :value="$stats['judge']" unit="Juri"
            description="Telah mengisi kuisioner evaluasi" color="purple" icon="academic-cap" />
    </div>

    {{-- Analisis Rating per Aspek & Distribusi Lapangan --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Kolom Kiri: Rating per Aspek --}}
        <section class="lg:col-span-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                        <flux:icon name="star" class="size-4" />
                    </div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Rata-rata Skor Bintang per Aspek (1 - 5)</h2>
                </div>
                <span class="text-xs font-mono font-bold text-amber-600">Skor Maks. 5.0</span>
            </div>

            @php
                $aspects = [
                    ['label' => 'Kepuasan Umum terhadap Sistem', 'score' => $stats['avg_overall']],
                    ['label' => 'Kemudahan Tampilan & Navigasi (UI/UX)', 'score' => $stats['avg_ui_ux']],
                    ['label' => 'Keandalan Penjurian & Presensi QR', 'score' => $stats['avg_features']],
                    ['label' => 'Kecepatan & Stabilitas di Lapangan', 'score' => $stats['avg_speed']],
                    ['label' => 'Objektivitas & Transparansi Hasil', 'score' => $stats['avg_transparency']],
                ];
            @endphp

            <div class="space-y-3.5">
                @foreach ($aspects as $asp)
                    @php
                        $pct = min(100, max(0, ($asp['score'] / 5) * 100));
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $asp['label'] }}</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">★ {{ number_format($asp['score'], 2) }}</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-linear-to-r from-amber-500 to-amber-600 transition-all duration-500"
                                style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Kolom Kanan: Kendala Utama & Fitur Paling Bermanfaat --}}
        <section class="lg:col-span-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400">
                        <flux:icon name="trophy" class="size-4" />
                    </div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Fitur yang Dirasa Paling Bermanfaat</h2>
                </div>
            </div>

            <div class="space-y-3">
                @forelse ($stats['features'] as $key => $item)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-zinc-700 dark:text-zinc-300 font-medium truncate max-w-[70%]">{{ $item['label'] }}</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $item['count'] }} suara ({{ $item['percent'] }}%)</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-500" style="width: {{ $item['percent'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-zinc-500 text-center py-4">Belum ada data suara respon fitur.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Grid 3 Kolom: Perangkat, Kendala, & Kesiapan Masa Depan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- 1. Perangkat --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
            <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5 border-b border-zinc-100 pb-2 dark:border-zinc-800">
                <flux:icon name="device-phone-mobile" class="size-4 text-zinc-400" />
                Perangkat yang Digunakan
            </h3>
            <div class="space-y-2">
                @foreach ($stats['devices'] as $dev)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ $dev['label'] }}</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $dev['count'] }} ({{ $dev['percent'] }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 2. Kendala Lapangan --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
            <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5 border-b border-zinc-100 pb-2 dark:border-zinc-800">
                <flux:icon name="exclamation-triangle" class="size-4 text-orange-500" />
                Kendala Utama di Lapangan
            </h3>
            <div class="space-y-2">
                @foreach ($stats['obstacles'] as $obs)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-zinc-600 dark:text-zinc-400 truncate max-w-[65%]" title="{{ $obs['label'] }}">{{ $obs['label'] }}</span>
                        <span class="font-bold text-orange-600 dark:text-orange-400">{{ $obs['count'] }} ({{ $obs['percent'] }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 3. Kesiapan Kelanjutan --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
            <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5 border-b border-zinc-100 pb-2 dark:border-zinc-800">
                <flux:icon name="arrow-trending-up" class="size-4 text-emerald-500" />
                Keberlanjutan di LT Berikutnya
            </h3>
            <div class="space-y-2">
                @foreach ($stats['readiness'] as $rd)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-zinc-600 dark:text-zinc-400 truncate max-w-[65%]" title="{{ $rd['label'] }}">{{ $rd['label'] }}</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $rd['count'] }} ({{ $rd['percent'] }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Daftar Responden & Umpan Balik Lengkap --}}
    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" class="size-5 text-amber-500" />
                    Daftar Respon &amp; Rekomendasi Fitur
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Rincian tanggapan perseorangan Panitia &amp; Dewan Juri</p>
            </div>

            {{-- Filter & Search --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                    <button type="button" wire:click="$set('roleFilter', 'all')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all {{ $roleFilter === 'all' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400' }}">
                        Semua
                    </button>
                    <button type="button" wire:click="$set('roleFilter', 'committee')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all {{ $roleFilter === 'committee' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400' }}">
                        Panitia
                    </button>
                    <button type="button" wire:click="$set('roleFilter', 'judge')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all {{ $roleFilter === 'judge' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400' }}">
                        Dewan Juri
                    </button>
                </div>

                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama / masukan..."
                        class="rounded-xl border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 shadow-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>
            </div>
        </div>

        @if ($responses->isEmpty())
            <div class="rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 p-8 text-center text-xs text-zinc-500 dark:text-zinc-400">
                Belum ada respon kuisioner yang sesuai dengan filter pencarian.
            </div>
        @else
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($responses as $resp)
                    <div class="py-4 space-y-2.5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">
                                    {{ $resp->user?->name ?: 'Responden' }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $resp->role === 'judge' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                    {{ $resp->roleLabel() }}
                                </span>
                                @if ($resp->duty_assignment)
                                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                        &bull; {{ $resp->duty_assignment }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                                    ★ {{ $resp->average_rating }} / 5.0
                                </span>
                                <span class="text-[11px] text-zinc-400">
                                    {{ $resp->updated_at?->translatedFormat('d M Y, H:i') }}
                                </span>
                                <flux:button wire:click="openDetail('{{ $resp->id }}')" size="xs" variant="subtle" icon="eye">
                                    Detail
                                </flux:button>
                            </div>
                        </div>

                        {{-- Cuplikan Rekomendasi Fitur --}}
                        @if ($resp->recommended_features)
                            <div class="rounded-xl bg-amber-50/50 p-2.5 text-xs text-zinc-800 dark:bg-amber-950/20 dark:text-zinc-200 border border-amber-200/50 dark:border-amber-900/30">
                                <strong class="text-amber-800 dark:text-amber-300 font-semibold">Rekomendasi Fitur:</strong>
                                <span class="italic">"{{ Str::limit($resp->recommended_features, 160) }}"</span>
                            </div>
                        @elseif ($resp->experience_feedback)
                            <div class="rounded-xl bg-zinc-50 p-2.5 text-xs text-zinc-700 dark:bg-zinc-800/40 dark:text-zinc-300">
                                <strong class="font-semibold text-zinc-800 dark:text-zinc-200">Kesan:</strong>
                                <span class="italic">"{{ Str::limit($resp->experience_feedback, 160) }}"</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="pt-3">
                {{ $responses->links() }}
            </div>
        @endif
    </section>

    {{-- Modal Detail Respon Kuisioner --}}
    @if ($showDetailModal && $this->selectedResponse)
        @php
            $sr = $this->selectedResponse;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 space-y-5 my-8">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $sr->role === 'judge' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' }}">
                            {{ $sr->roleLabel() }}
                        </span>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                            {{ $sr->user?->name ?: 'Responden' }}
                        </h3>
                        <p class="text-xs text-zinc-500">{{ $sr->duty_assignment ?: 'Penugasan tidak ditulis' }} &bull; NTA: {{ $sr->user?->scout_id ?: '-' }}</p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-6" />
                    </button>
                </div>

                {{-- Rating Rinci --}}
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Rating Bintang (Skala 1 - 5)</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                            <span class="text-zinc-500">Kepuasan Umum:</span>
                            <span class="font-bold text-amber-600 float-right">★ {{ $sr->rating_overall }} / 5</span>
                        </div>
                        <div class="p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                            <span class="text-zinc-500">Kemudahan UI/UX:</span>
                            <span class="font-bold text-amber-600 float-right">★ {{ $sr->rating_ui_ux }} / 5</span>
                        </div>
                        <div class="p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                            <span class="text-zinc-500">Keandalan Fitur:</span>
                            <span class="font-bold text-amber-600 float-right">★ {{ $sr->rating_features }} / 5</span>
                        </div>
                        <div class="p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                            <span class="text-zinc-500">Kecepatan Lapangan:</span>
                            <span class="font-bold text-amber-600 float-right">★ {{ $sr->rating_speed }} / 5</span>
                        </div>
                        <div class="p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 col-span-2">
                            <span class="text-zinc-500">Objektivitas &amp; Transparansi:</span>
                            <span class="font-bold text-amber-600 float-right">★ {{ $sr->rating_transparency }} / 5</span>
                        </div>
                    </div>
                </div>

                {{-- Pilihan Ganda --}}
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Pilihan Ganda</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-lg border border-zinc-200/60 dark:border-zinc-800">
                            <dt class="text-zinc-400 text-[10px]">Perangkat:</dt>
                            <dd class="font-bold text-zinc-800 dark:text-zinc-200 mt-0.5">{{ \App\Models\QuestionnaireResponse::DEVICES[$sr->device_used] ?? $sr->device_used }}</dd>
                        </div>
                        <div class="p-2.5 rounded-lg border border-zinc-200/60 dark:border-zinc-800">
                            <dt class="text-zinc-400 text-[10px]">Tingkat Efisiensi:</dt>
                            <dd class="font-bold text-zinc-800 dark:text-zinc-200 mt-0.5">{{ \App\Models\QuestionnaireResponse::EFFICIENCY_LEVELS[$sr->efficiency_level] ?? $sr->efficiency_level }}</dd>
                        </div>
                        <div class="p-2.5 rounded-lg border border-zinc-200/60 dark:border-zinc-800 sm:col-span-2">
                            <dt class="text-zinc-400 text-[10px]">Kendala Utama:</dt>
                            <dd class="font-bold text-orange-700 dark:text-orange-400 mt-0.5">{{ \App\Models\QuestionnaireResponse::OBSTACLES[$sr->main_obstacle] ?? $sr->main_obstacle }}</dd>
                        </div>
                        <div class="p-2.5 rounded-lg border border-zinc-200/60 dark:border-zinc-800 sm:col-span-2">
                            <dt class="text-zinc-400 text-[10px]">Fitur Paling Bermanfaat:</dt>
                            <dd class="font-bold text-indigo-700 dark:text-indigo-400 mt-0.5">{{ \App\Models\QuestionnaireResponse::MOST_USEFUL_FEATURES[$sr->most_useful_feature] ?? $sr->most_useful_feature }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Masukan Esai --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Masukan Naratif</h4>
                    <div class="space-y-2 text-xs">
                        <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200/60 dark:bg-amber-950/30 dark:border-amber-800/40">
                            <strong class="text-amber-900 dark:text-amber-300 block mb-1">Rekomendasi Fitur Masa Depan:</strong>
                            <p class="text-zinc-800 dark:text-zinc-200 whitespace-pre-line">{{ $sr->recommended_features ?: 'Tidak ada catatan.' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/60 dark:bg-zinc-800/50 dark:border-zinc-700/60">
                            <strong class="text-zinc-800 dark:text-zinc-200 block mb-1">Kesan &amp; Pengalaman Lapangan:</strong>
                            <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ $sr->experience_feedback ?: 'Tidak ada catatan.' }}</p>
                        </div>
                        @if ($sr->general_suggestions)
                            <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/60 dark:bg-zinc-800/50 dark:border-zinc-700/60">
                                <strong class="text-zinc-800 dark:text-zinc-200 block mb-1">Kritik &amp; Saran Tambahan:</strong>
                                <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ $sr->general_suggestions }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button wire:click="closeDetail" variant="subtle" icon="x-mark">
                        Tutup Rincian
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
