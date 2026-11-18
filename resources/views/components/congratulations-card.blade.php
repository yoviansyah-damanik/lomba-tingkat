@props(['honor' => []])

@php
    $rank = (int) ($honor['rank'] ?? 1);
    $isHighest = !empty($honor['is_highest']);

    $styles = match ($rank) {
        1 => [
            'card' => 'border-amber-400 bg-linear-to-r from-amber-500/15 via-amber-400/10 to-yellow-500/10 dark:border-amber-500/50 dark:from-amber-950/40 dark:via-amber-900/20 dark:to-yellow-950/20 ring-1 ring-amber-400/30',
            'badge' => 'bg-amber-500 text-white shadow-xs',
            'badge_text' => '🏆 Peringkat 1 • Regu Berprestasi Tinggi',
            'icon_bg' => 'bg-amber-500 text-white',
            'icon' => 'trophy',
            'title_color' => 'text-amber-900 dark:text-amber-200',
            'highlight_box' => 'border-amber-300/80 bg-amber-50/80 dark:border-amber-700/60 dark:bg-amber-950/40 text-amber-950 dark:text-amber-100',
        ],
        2 => [
            'card' => 'border-slate-300 bg-linear-to-r from-slate-200/50 via-zinc-100/50 to-slate-200/30 dark:border-slate-600 dark:from-slate-900/50 dark:via-zinc-900/30 dark:to-slate-900/30 ring-1 ring-slate-300/40',
            'badge' => 'bg-slate-600 text-white shadow-xs',
            'badge_text' => '🥈 Peringkat 2 • Regu Berprestasi Baik',
            'icon_bg' => 'bg-slate-600 text-white',
            'icon' => 'star',
            'title_color' => 'text-slate-900 dark:text-slate-100',
            'highlight_box' => 'border-slate-300 bg-slate-50/80 dark:border-slate-700 dark:bg-slate-800/40 text-slate-900 dark:text-slate-100',
        ],
        3 => [
            'card' => 'border-orange-300 bg-linear-to-r from-orange-400/15 via-amber-600/10 to-orange-400/10 dark:border-orange-700/60 dark:from-orange-950/40 dark:via-amber-950/20 dark:to-orange-950/20 ring-1 ring-orange-400/30',
            'badge' => 'bg-orange-600 text-white shadow-xs',
            'badge_text' => '🥉 Peringkat 3 • Regu Berprestasi Cukup',
            'icon_bg' => 'bg-orange-600 text-white',
            'icon' => 'sparkles',
            'title_color' => 'text-orange-950 dark:text-orange-200',
            'highlight_box' => 'border-orange-300 bg-orange-50/80 dark:border-orange-800/50 dark:bg-orange-950/30 text-orange-950 dark:text-orange-100',
        ],
        default => [
            'card' => 'border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900',
            'badge' => 'bg-zinc-600 text-white',
            'badge_text' => 'Regu Berprestasi',
            'icon_bg' => 'bg-zinc-600 text-white',
            'icon' => 'trophy',
            'title_color' => 'text-zinc-900 dark:text-zinc-100',
            'highlight_box' => 'border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800 text-zinc-900',
        ],
    };
@endphp

<div class="relative overflow-hidden rounded-2xl border p-5 sm:p-6 shadow-sm transition-all duration-200 {{ $styles['card'] }}">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl {{ $styles['icon_bg'] }} shadow-md">
                <flux:icon :name="$styles['icon']" class="size-7" />
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-bold {{ $styles['badge'] }}">
                        {{ $styles['badge_text'] }}
                    </span>
                    <span class="text-xs font-mono font-semibold text-zinc-600 dark:text-zinc-400">
                        NPP: {{ $honor['npp'] }}
                    </span>
                    <span class="text-xs text-zinc-400">&bull;</span>
                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        {{ $honor['school_name'] }}
                    </span>
                </div>

                <div>
                    <h3 class="text-lg font-black tracking-tight sm:text-xl {{ $styles['title_color'] }}">
                        🎉 Selamat &amp; Sukses atas Raihan Predikat Kehormatan!
                    </h3>
                    <p class="mt-1 text-xs text-zinc-700 sm:text-sm dark:text-zinc-300 leading-relaxed max-w-3xl">
                        Selamat kepada <strong>Regu {{ $honor['patrol_name'] }} ({{ $honor['gender'] }})</strong> dari pangkalan <strong>{{ $honor['school_name'] }}</strong> atas pencapaian luar biasa yang berhasil meraih predikat resmi:
                    </p>
                </div>

                {{-- Banner Predikat Resmi --}}
                <div class="rounded-xl border p-3.5 {{ $styles['highlight_box'] }} space-y-1">
                    <div class="text-base sm:text-lg font-black uppercase tracking-wide">
                        {{ $honor['title'] }}
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs opacity-90">
                        <span>Akumulasi: <strong>{{ $honor['total_points'] }} Poin</strong></span>
                        <span>&bull;</span>
                        <span>Perolehan: <strong>{{ $honor['gold'] }} Emas, {{ $honor['silver'] }} Perak, {{ $honor['bronze'] }} Perunggu</strong></span>
                    </div>
                </div>

                {{-- Pesan Khusus Regu Berprestasi Tinggi (Tiket ke LT-IV) --}}
                @if ($isHighest)
                    <div class="rounded-xl bg-amber-500/10 border border-amber-500/30 p-3 text-xs text-amber-950 dark:text-amber-200 leading-relaxed">
                        🌟 <strong>Panggilan Lomba Tingkat IV:</strong> Sebagai <strong>Regu Berprestasi Tinggi</strong>, regu ini berhak mewakili Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan menuju <strong>Lomba Tingkat IV (LT-IV) Kwartir Daerah Gerakan Pramuka Sumatera Utara</strong>. Teruslah berlatih, jaga kekompakan, dan harumkan nama Kwartir Cabang Kota Padangsidimpuan!
                    </div>
                @else
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 italic">
                        Pertahankan semangat Tri Satya dan Dasa Darma Pramuka, serta terus tingkatkan keterampilan kepramukaan untuk menginspirasi rekan-rekan penggalang lainnya.
                    </p>
                @endif
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex flex-row sm:flex-col gap-2 shrink-0 self-end sm:self-start">
            @if (!empty($honor['is_certificate_published']))
                <flux:button href="{{ $honor['download_url'] }}" size="sm" variant="primary" icon="academic-cap">
                    Unduh E-Sertifikat
                </flux:button>
            @endif
            <flux:button href="{{ route('standings') }}" size="sm" variant="outline" icon="chart-bar">
                Lihat Klasemen
            </flux:button>
        </div>
    </div>
</div>
