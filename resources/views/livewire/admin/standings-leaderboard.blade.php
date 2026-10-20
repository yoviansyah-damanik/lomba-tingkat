<div class="space-y-6">
    {{-- Header Halaman Dasbor --}}
    <x-heading-section
        title="Rekap Nilai & Klasemen Prestasi"
        description="Pusat data hasil perlombaan, perolehan medali predikat resmi, dan klasemen juara umum LT III."
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
            <flux:button type="button" variant="subtle" icon="printer" onclick="window.print()">
                Cetak Lembar
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
                wire:click="$set('activeTab', 'contingents')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'contingents' ? 'bg-amber-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                <flux:icon name="building-office" class="size-4" />
                <span>Klasemen Kontingen (Gudep)</span>
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'patrols')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'patrols' ? 'bg-amber-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                <flux:icon name="user-group" class="size-4" />
                <span>Klasemen Regu</span>
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'competitions')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'competitions' ? 'bg-amber-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                <flux:icon name="clipboard-document-list" class="size-4" />
                <span>Rekap Nilai Mata Lomba</span>
            </button>
        </div>

        {{-- Filter Kategori Gender (untuk tab kontingen & regu) --}}
        @if($activeTab !== 'competitions')
            <div class="flex items-center gap-1.5 self-start sm:self-auto">
                <button
                    type="button"
                    wire:click="$set('genderFilter', 'all')"
                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition {{ $genderFilter === 'all' ? 'bg-zinc-800 text-white dark:bg-zinc-200 dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                    Semua
                </button>
                <button
                    type="button"
                    wire:click="$set('genderFilter', 'boys')"
                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition {{ $genderFilter === 'boys' ? 'bg-blue-600 text-white' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                    Putra
                </button>
                <button
                    type="button"
                    wire:click="$set('genderFilter', 'girls')"
                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition {{ $genderFilter === 'girls' ? 'bg-rose-600 text-white' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                    Putri
                </button>
            </div>
        @endif
    </div>

    {{-- ================= TAB 1: KLASEMEN KONTINGEN (GUDEP) ================= --}}
    @if($activeTab === 'contingents')
        {{-- Visual Top 3 Podium --}}
        @if($contingents->count() >= 3 && empty($search))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                {{-- Juara Tergiat 2 (Perak) --}}
                @php $second = $contingents->get(1); @endphp
                @if($second)
                    <div class="order-2 md:order-1 rounded-2xl border border-zinc-200/80 bg-gradient-to-b from-slate-100/80 via-white to-white p-5 text-center dark:border-zinc-800 dark:from-zinc-900 dark:via-zinc-900/90 dark:to-zinc-950 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 size-20 rounded-full bg-slate-300/20 dark:bg-slate-700/20 blur-xl"></div>
                        <div class="inline-flex size-10 items-center justify-center rounded-2xl bg-slate-400 text-white font-black text-lg shadow-sm mx-auto mb-3">
                            2
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300 mb-2">
                            🥈 Regu Prestasi Baik
                        </span>
                        <h3 class="font-extrabold text-base text-zinc-900 dark:text-zinc-100 leading-snug line-clamp-2" title="{{ $second['school_name'] }}">
                            {{ $second['school_name'] }}
                        </h3>
                        <p class="text-xs text-zinc-500 mt-1">Gudep: {{ $second['group_number'] }}</p>
                        <div class="mt-4 pt-3 border-t border-zinc-200/60 dark:border-zinc-800/80 flex justify-around text-xs">
                            <div>
                                <span class="text-zinc-400 block text-[10px] font-bold">MEDALI</span>
                                <span class="font-extrabold text-zinc-800 dark:text-zinc-200">{{ $second['gold'] }} 🥇 / {{ $second['silver'] }} 🥈</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-[10px] font-bold">TOTAL POIN</span>
                                <span class="font-extrabold text-amber-600 dark:text-amber-400 text-sm">{{ $second['total_points'] }} Pts</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Juara Tergiat 1 (Emas) --}}
                @php $first = $contingents->first(); @endphp
                @if($first)
                    <div class="order-1 md:order-2 rounded-2xl border-2 border-amber-500/80 bg-gradient-to-b from-amber-100/60 via-amber-50/20 to-white p-6 text-center dark:border-amber-500/50 dark:from-amber-950/40 dark:via-zinc-900 dark:to-zinc-950 shadow-md relative overflow-hidden -mt-2">
                        <div class="absolute -right-4 -top-4 size-24 rounded-full bg-amber-400/20 blur-xl"></div>
                        <div class="inline-flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white font-black text-xl shadow-md mx-auto mb-3">
                            👑
                        </div>
                        <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-black uppercase bg-amber-500 text-white shadow-xs mb-2">
                            🥇 Regu Prestasi Tinggi
                        </span>
                        <h3 class="font-black text-lg text-zinc-900 dark:text-zinc-100 leading-snug line-clamp-2" title="{{ $first['school_name'] }}">
                            {{ $first['school_name'] }}
                        </h3>
                        <p class="text-xs text-amber-900/80 dark:text-amber-300/80 font-medium mt-1">Gudep: {{ $first['group_number'] }} • {{ $first['kwarran_name'] }}</p>
                        <div class="mt-4 pt-3 border-t border-amber-200/60 dark:border-amber-900/40 flex justify-around text-xs">
                            <div>
                                <span class="text-amber-800/70 dark:text-amber-400/70 block text-[10px] font-bold">MEDALI</span>
                                <span class="font-black text-zinc-900 dark:text-zinc-100 text-sm">{{ $first['gold'] }} 🥇 / {{ $first['silver'] }} 🥈 / {{ $first['bronze'] }} 🥉</span>
                            </div>
                            <div>
                                <span class="text-amber-800/70 dark:text-amber-400/70 block text-[10px] font-bold">TOTAL POIN</span>
                                <span class="font-black text-amber-600 dark:text-amber-400 text-base">{{ $first['total_points'] }} Pts</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Juara Tergiat 3 (Perunggu) --}}
                @php $third = $contingents->get(2); @endphp
                @if($third)
                    <div class="order-3 rounded-2xl border border-zinc-200/80 bg-gradient-to-b from-amber-50/40 via-white to-white p-5 text-center dark:border-zinc-800 dark:from-zinc-900 dark:via-zinc-900/90 dark:to-zinc-950 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 size-20 rounded-full bg-amber-700/20 blur-xl"></div>
                        <div class="inline-flex size-10 items-center justify-center rounded-2xl bg-amber-700 text-white font-black text-lg shadow-sm mx-auto mb-3">
                            3
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300 mb-2">
                            🥉 Regu Prestasi Cukup
                        </span>
                        <h3 class="font-extrabold text-base text-zinc-900 dark:text-zinc-100 leading-snug line-clamp-2" title="{{ $third['school_name'] }}">
                            {{ $third['school_name'] }}
                        </h3>
                        <p class="text-xs text-zinc-500 mt-1">Gudep: {{ $third['group_number'] }}</p>
                        <div class="mt-4 pt-3 border-t border-zinc-200/60 dark:border-zinc-800/80 flex justify-around text-xs">
                            <div>
                                <span class="text-zinc-400 block text-[10px] font-bold">MEDALI</span>
                                <span class="font-extrabold text-zinc-800 dark:text-zinc-200">{{ $third['gold'] }} 🥇 / {{ $third['silver'] }} 🥈 / {{ $third['bronze'] }} 🥉</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-[10px] font-bold">TOTAL POIN</span>
                                <span class="font-extrabold text-amber-600 dark:text-amber-400 text-sm">{{ $third['total_points'] }} Pts</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Tabel Klasemen Kontingen Lengkap --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Daftar Peringkat Kontingen Gugus Depan</span>
                    <span class="text-xs text-zinc-500">({{ $contingents->count() }} Pangkalan Terverifikasi)</span>
                </div>
                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari pangkalan..."
                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 dark:text-zinc-400 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-3.5 text-center w-12">Rank</th>
                            <th class="py-3 px-4">Pangkalan / Gugus Depan</th>
                            <th class="py-3 px-3 text-center">Kwarran</th>
                            <th class="py-3 px-3 text-center text-amber-600 dark:text-amber-400">Tinggi (🥇)</th>
                            <th class="py-3 px-3 text-center text-slate-500 dark:text-slate-300">Baik (🥈)</th>
                            <th class="py-3 px-3 text-center text-amber-700 dark:text-amber-500">Cukup (🥉)</th>
                            <th class="py-3 px-3 text-center">Total Medali</th>
                            <th class="py-3 px-4 text-center font-black">Total Poin</th>
                            <th class="py-3 px-4 text-right">Nilai Akumulasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 font-medium">
                        @forelse($contingents as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition {{ $item['rank'] <= 3 ? 'bg-amber-50/20 dark:bg-amber-950/10' : '' }}">
                                <td class="py-3 px-3.5 text-center">
                                    @if($item['rank'] === 1)
                                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-amber-500 text-white font-black text-xs shadow-xs">1</span>
                                    @elseif($item['rank'] === 2)
                                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-slate-400 text-white font-black text-xs">2</span>
                                    @elseif($item['rank'] === 3)
                                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-amber-700 text-white font-black text-xs">3</span>
                                    @else
                                        <span class="text-zinc-500 font-bold">{{ $item['rank'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $item['school_name'] }}</div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5">Gudep: {{ $item['group_number'] }} • {{ $item['patrols_count'] }} Regu</div>
                                </td>
                                <td class="py-3 px-3 text-center text-zinc-600 dark:text-zinc-400">
                                    {{ $item['kwarran_name'] }}
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
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-lg font-black text-amber-700 bg-amber-50 border border-amber-200 dark:bg-amber-950/60 dark:border-amber-800 dark:text-amber-300">
                                        {{ $item['total_points'] }} Pts
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($item['total_score_sum'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-zinc-500">
                                    Tidak ada data pangkalan yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ================= TAB 2: KLASEMEN REGU ================= --}}
    @if($activeTab === 'patrols')
        <div class="rounded-2xl border border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Peringkat Prestasi Regu Penggalang</span>
                    <span class="text-xs text-zinc-500">({{ $patrols->count() }} Regu)</span>
                </div>
                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari regu atau sekolah..."
                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 dark:text-zinc-400 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-3.5 text-center w-12">Rank</th>
                            <th class="py-3 px-4">Nama Regu & Kategori</th>
                            <th class="py-3 px-4">Pangkalan / Gudep</th>
                            <th class="py-3 px-3 text-center text-amber-600 dark:text-amber-400">Tinggi (🥇)</th>
                            <th class="py-3 px-3 text-center text-slate-500 dark:text-slate-300">Baik (🥈)</th>
                            <th class="py-3 px-3 text-center text-amber-700 dark:text-amber-500">Cukup (🥉)</th>
                            <th class="py-3 px-3 text-center">Total Medali</th>
                            <th class="py-3 px-4 text-center font-black">Total Poin</th>
                            <th class="py-3 px-4 text-right">Nilai Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 font-medium">
                        @forelse($patrols as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3 px-3.5 text-center">
                                    <span class="font-extrabold text-xs {{ $item['rank'] <= 3 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500' }}">
                                        #{{ $item['rank'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm flex items-center gap-2">
                                        <span>{{ $item['patrol_name'] }}</span>
                                        @if($item['gender_category'] === 'boys')
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/15 text-blue-600 border border-blue-500/30">Putra</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30">Putri</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5">{{ $item['competitions_scored'] }} Lomba Dinilai</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $item['school_name'] }}</div>
                                    <div class="text-[10px] text-zinc-500">Gudep: {{ $item['group_number'] }}</div>
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
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md font-bold text-amber-700 bg-amber-50 border border-amber-200 dark:bg-amber-950/60 dark:border-amber-800 dark:text-amber-300">
                                        {{ $item['total_points'] }} Pts
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($item['total_score_sum'], 2) }}
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
    @endif

    {{-- ================= TAB 3: REKAP NILAI MATA LOMBA ================= --}}
    @if($activeTab === 'competitions')
        <div class="space-y-4">
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
                                [{{ $c->code }}] {{ $c->title }} ({{ $c->scores_count }} dinilai)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
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

                {{-- Tabel Rekapitulasi Nilai Lomba --}}
                <div class="rounded-2xl border border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden shadow-xs">
                    <div class="p-3.5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-900/50">
                        <span class="font-bold text-xs uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                            Lembar Rekapitulasi Nilai Seluruh Regu Peserta
                        </span>
                        <span class="text-xs text-zinc-500">{{ $recap->count() }} Regu Dinilai</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 dark:text-zinc-400 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                                <tr>
                                    <th class="py-3 px-3.5 text-center w-12">Rank</th>
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
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                                {{ $score->patrol?->patrol_name }}
                                            </div>
                                            <div class="text-[11px] text-zinc-500 mt-0.5">
                                                {{ $score->patrol?->scoutGroup?->school_name ?? '-' }} (Gudep {{ $score->patrol?->group_number }})
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
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase border {{ $score->predicateBadgeClasses() }}">
                                                {{ $score->predicateLabel() }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-zinc-600 dark:text-zinc-400">
                                            <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                                {{ $score->judge?->name ?? 'Juri Pos' }}
                                            </div>
                                            @if($score->notes)
                                                <div class="text-[11px] text-zinc-500 italic mt-0.5 line-clamp-1">
                                                    "{{ $score->notes }}"
                                                </div>
                                            @endif
                                        </td>
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
                                                    <span class="text-zinc-400 text-xs">Final</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-10 text-center text-zinc-500">
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
                                    Regu {{ $p->patrol_name }} ({{ $p->scoutGroup?->school_name }})
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
</div>
