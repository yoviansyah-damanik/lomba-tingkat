<x-layouts::app title="Verifikasi Pendaftaran Kontingen - Admin">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-official-header />

        {{-- Page Header --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Verifikasi Pendaftaran Kontingen
                </h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Pemeriksaan kelengkapan berkas regu, peserta penggalang, dan bindamping pangkalan Gugus Depan LT III.
                </p>
            </div>
        </div>

        {{-- Status Filter Tabs & Summary Counters --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            <a href="{{ route('admin.scout-groups.index') }}"
               class="rounded-xl border p-3.5 transition {{ !request('status') ? 'border-amber-500 bg-amber-50/60 dark:border-amber-500/60 dark:bg-amber-950/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900' }}">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Semua Pangkalan</div>
                <div class="mt-1 text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $statusCounts['all'] }}</div>
            </a>
            <a href="{{ route('admin.scout-groups.index', ['status' => 'submitted']) }}"
               class="rounded-xl border p-3.5 transition {{ request('status') === 'submitted' ? 'border-blue-500 bg-blue-50/60 dark:border-blue-500/60 dark:bg-blue-950/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900' }}">
                <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Menunggu Verifikasi</div>
                <div class="mt-1 text-xl font-bold text-blue-700 dark:text-blue-300">{{ $statusCounts['submitted'] }}</div>
            </a>
            <a href="{{ route('admin.scout-groups.index', ['status' => 'verified']) }}"
               class="rounded-xl border p-3.5 transition {{ request('status') === 'verified' ? 'border-emerald-500 bg-emerald-50/60 dark:border-emerald-500/60 dark:bg-emerald-950/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900' }}">
                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Telah Disetujui</div>
                <div class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ $statusCounts['verified'] }}</div>
            </a>
            <a href="{{ route('admin.scout-groups.index', ['status' => 'needs_revision']) }}"
               class="rounded-xl border p-3.5 transition {{ request('status') === 'needs_revision' ? 'border-orange-500 bg-orange-50/60 dark:border-orange-500/60 dark:bg-orange-950/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900' }}">
                <div class="text-xs text-orange-600 dark:text-orange-400 font-semibold">Perlu Perbaikan</div>
                <div class="mt-1 text-xl font-bold text-orange-700 dark:text-orange-300">{{ $statusCounts['needs_revision'] }}</div>
            </a>
            <a href="{{ route('admin.scout-groups.index', ['status' => 'rejected']) }}"
               class="rounded-xl border p-3.5 transition {{ request('status') === 'rejected' ? 'border-red-500 bg-red-50/60 dark:border-red-500/60 dark:bg-red-950/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900' }}">
                <div class="text-xs text-red-600 dark:text-red-400 font-semibold">Ditolak</div>
                <div class="mt-1 text-xl font-bold text-red-700 dark:text-red-300">{{ $statusCounts['rejected'] }}</div>
            </a>
        </div>

        {{-- Search & Filter Toolbar --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <form method="GET" action="{{ route('admin.scout-groups.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari nama sekolah / pangkalan / no. gudep / kode reg..."
                           class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 placeholder-zinc-400 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-700">
                        Cari
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.scout-groups.index') }}" class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            Atur Ulang
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Contingents Table --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-zinc-200 bg-zinc-50 text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Kode & Pangkalan</th>
                            <th class="px-4 py-3 font-semibold">Nomor Gudep</th>
                            <th class="px-4 py-3 font-semibold">PIC / Kontak</th>
                            <th class="px-4 py-3 font-semibold">Regu & Peserta</th>
                            <th class="px-4 py-3 font-semibold">Bindamping</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($scoutGroups as $group)
                            @php
                                $totalParticipants = $group->patrols->sum(fn($p) => $p->participants->count());
                            @endphp
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-[11px] font-bold text-amber-700 dark:text-amber-400">
                                        {{ $group->registration_code }}
                                    </span>
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $group->school_name }}
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                        {{ $group->address }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ $group->group_number }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $group->contact_person }}</div>
                                    <div class="text-[11px] text-zinc-500">{{ $group->contact_phone }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                            {{ $group->patrols->count() }} Regu
                                        </span>
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $totalParticipants }} Peserta
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                                        {{ $group->chaperones->count() }} Orang
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-[11px] font-semibold {{ $group->verification_status?->badgeClasses() }}">
                                        {{ $group->verification_status?->label() }}
                                    </span>
                                    @if($group->verified_at)
                                        <div class="mt-0.5 text-[10px] text-zinc-400">
                                            {{ $group->verified_at->translatedFormat('d M Y, H:i') }} WIB
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.scout-groups.show', $group->id) }}"
                                       class="inline-flex items-center rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                        Periksa Berkas &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-xs text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data pangkalan Gugus Depan yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($scoutGroups->hasPages())
                <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                    {{ $scoutGroups->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
