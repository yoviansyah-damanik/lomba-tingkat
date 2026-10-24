@php
    use App\Models\CompetitionScore;

    // Baris tabel bobot: [label peringkat, wakil peringkat untuk menghitung bobot]
    $weightRows = [
        ['1 (Tinggi)', 1], ['2 (Baik)', 2], ['3 (Cukup)', 3], ['4', 4], ['5', 5], ['6', 6],
        ['7', 7], ['8', 8], ['9', 9], ['10', 10], ['11 - 20', 11], ['21 - 33', 21],
    ];
@endphp

{{-- Keterangan penentuan peringkat umum (poin per peringkat lomba dan aturan seri) --}}
<details {{ $attributes->class('group rounded-2xl border border-zinc-200 bg-white text-xs shadow-xs dark:border-zinc-800 dark:bg-zinc-900') }}>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-3 font-bold text-zinc-800 dark:text-zinc-200">
        <span class="inline-flex items-center gap-2">
            <flux:icon name="information-circle" class="size-4 text-amber-600" />
            Cara Penentuan Peringkat Umum
        </span>
        <flux:icon name="chevron-down" class="size-4 text-zinc-400 transition group-open:rotate-180" />
    </summary>

    <div class="grid grid-cols-1 gap-5 border-t border-zinc-100 px-4 py-4 md:grid-cols-2 dark:border-zinc-800">
        <ol class="list-outside list-[lower-alpha] space-y-3 pl-4 leading-relaxed text-zinc-600 dark:text-zinc-400">
            <li>
                Peringkat umum ditentukan oleh <strong class="text-zinc-800 dark:text-zinc-200">total poin</strong>, yaitu jumlah nilai bobot dari seluruh lomba.
                Setiap pencapaian (peringkat dalam satu lomba) dikonversi menjadi poin sesuai tabel nilai bobot.
            </li>
            <li>
                Dalam hal terjadi <strong class="text-zinc-800 dark:text-zinc-200">kesamaan total poin</strong>, penentunya adalah akumulasi pencapaian pada lomba-lomba, dibandingkan secara berurut:
                jumlah lomba <strong>Prestasi Tinggi</strong> terbanyak, lalu <strong>Prestasi Baik</strong> terbanyak, lalu <strong>Prestasi Cukup</strong> terbanyak.
            </li>
            <li>
                Peringkat regu putra dan regu putri dihitung terpisah.
            </li>
        </ol>

        <div>
            <p class="mb-1.5 font-semibold text-zinc-700 dark:text-zinc-300">Nilai bobot per peringkat lomba</p>
            <table class="w-full max-w-xs border-collapse text-left">
                <thead>
                    <tr class="border-b border-zinc-200 text-[10px] uppercase tracking-wider text-zinc-500 dark:border-zinc-700">
                        <th class="py-1.5 pr-3 font-semibold">Peringkat</th>
                        <th class="py-1.5 text-right font-semibold">Nilai Bobot</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($weightRows as [$label, $rank])
                        <tr>
                            <td class="py-1 pr-3 text-zinc-700 dark:text-zinc-300">{{ $label }}</td>
                            <td class="py-1 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ CompetitionScore::rankWeight($rank) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</details>
