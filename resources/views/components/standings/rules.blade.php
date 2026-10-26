@php
    use App\Models\CompetitionScore;

    // Baris tabel bobot: [label peringkat, wakil peringkat untuk menghitung bobot]
    $weightRows = [
        ['1 (Tinggi)', 1], ['2 (Baik)', 2], ['3 (Cukup)', 3], ['4', 4], ['5', 5], ['6', 6],
        ['7', 7], ['8', 8], ['9', 9], ['10', 10], ['11 - 20', 11], ['21 - 33', 21], ['34 ke atas', 34],
    ];
@endphp

{{-- Keterangan penentuan peringkat umum (poin per peringkat lomba dan aturan seri) dalam modal --}}
<div {{ $attributes }}>
    {{-- Banner pemicu: berdiri sendiri di luar tab dan filter --}}
    <div class="relative overflow-hidden rounded-2xl border border-amber-300/70 bg-linear-to-r from-amber-50 via-white to-amber-50/60 p-5 shadow-xs dark:border-amber-500/20 dark:from-amber-950/30 dark:via-zinc-900 dark:to-zinc-900">
        <div class="pointer-events-none absolute -top-10 -right-10 size-40 rounded-full bg-amber-400/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-amber-400 to-amber-600 text-white shadow-md shadow-amber-600/25">
                    <flux:icon name="scale" class="size-6" />
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400">Transparansi Penilaian</span>
                    <h3 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">Bagaimana Peringkat Umum Ditentukan?</h3>

                    {{-- Ringkasan alur: poin lalu pemecah seri --}}
                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                        <span class="rounded-full bg-zinc-900 px-2.5 py-1 text-white dark:bg-zinc-100 dark:text-zinc-900">Total Poin</span>
                        <flux:icon name="chevron-right" class="size-3 text-zinc-400" />
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">🥇 Tinggi</span>
                        <flux:icon name="chevron-right" class="size-3 text-zinc-400" />
                        <span class="rounded-full bg-zinc-200 px-2.5 py-1 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">🥈 Baik</span>
                        <flux:icon name="chevron-right" class="size-3 text-zinc-400" />
                        <span class="rounded-full bg-orange-100 px-2.5 py-1 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">🥉 Cukup</span>
                    </div>
                </div>
            </div>

            <flux:modal.trigger name="standings-rules">
                <flux:button variant="primary" icon="information-circle" class="shrink-0">Lihat Cara Penentuan</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <flux:modal name="standings-rules" class="w-full max-w-3xl !p-0 overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-amber-100 bg-linear-to-r from-amber-50 to-white px-6 py-5 dark:border-zinc-800 dark:from-amber-950/30 dark:to-zinc-900">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm">
                <flux:icon name="trophy" class="size-6" />
            </div>
            <div>
                <flux:heading size="lg">Cara Penentuan Peringkat Umum</flux:heading>
                <flux:text class="text-xs">Bagaimana poin dihitung dan siapa yang unggul saat nilai sama.</flux:text>
            </div>
        </div>

        <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-5 text-xs">
            {{-- Langkah penentuan --}}
            <ol class="space-y-3">
                @php
                    $steps = [
                        ['Total poin menentukan peringkat', 'Setiap peringkat yang diraih pada satu lomba dikonversi menjadi poin sesuai tabel nilai bobot, lalu seluruh poin dijumlahkan.'],
                        ['Jika total poin sama', 'Akumulasi pencapaian dibandingkan secara berurut sebagai penentu, seperti pada urutan di bawah.'],
                        ['Putra & putri terpisah', 'Peringkat regu putra dan regu putri dihitung masing-masing.'],
                    ];
                @endphp
                @foreach ($steps as $n => [$title, $desc])
                    <li class="flex gap-3 rounded-xl border border-zinc-200 bg-zinc-50/60 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ $n + 1 }}</span>
                        <div>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $title }}</p>
                            <p class="mt-0.5 leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $desc }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            {{-- Urutan pemecah seri --}}
            <div>
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-zinc-500">Urutan pemecah seri</p>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 font-semibold text-amber-800 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:ring-amber-800">🥇 Prestasi Tinggi terbanyak</span>
                    <flux:icon name="chevron-right" class="size-3.5 text-zinc-400" />
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1.5 font-semibold text-zinc-700 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700">🥈 Prestasi Baik terbanyak</span>
                    <flux:icon name="chevron-right" class="size-3.5 text-zinc-400" />
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 font-semibold text-orange-800 ring-1 ring-orange-200 dark:bg-orange-900/20 dark:text-orange-300 dark:ring-orange-800">🥉 Prestasi Cukup terbanyak</span>
                </div>
            </div>

            {{-- Tabel bobot --}}
            <div>
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-zinc-500">Nilai bobot per peringkat lomba</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($weightRows as [$label, $rank])
                        <div @class([
                            'flex items-center justify-between rounded-lg border px-3 py-2',
                            'border-amber-300 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' => $rank <= 3,
                            'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' => $rank > 3,
                        ])>
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $label }}</span>
                            <span class="font-mono text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ CompetitionScore::rankWeight($rank) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end border-t border-zinc-100 bg-zinc-50 px-6 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:modal.close>
                <flux:button variant="primary" size="sm" icon="check">Mengerti</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>
