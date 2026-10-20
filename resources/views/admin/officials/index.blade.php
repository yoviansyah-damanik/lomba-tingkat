<x-layouts::app title="Aparatur Panitia & Dewan Juri">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                    Aparatur Panitia & Dewan Juri
                </h1>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 sm:text-sm">
                    Kelola akun panitia pelaksana, dewan juri, serta penerbitan Kartu Tanda Pengenal (ID Card Digital QR)
                </p>
            </div>
            <div>
                <a href="{{ route('admin.officials.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                    <flux:icon name="user-plus" class="size-4" />
                    + Tambah Aparatur Baru
                </a>
            </div>
        </div>

        {{-- Notification Alerts --}}
        <x-alert />

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Aparatur</span>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['total'] }}</div>
                <div class="text-[11px] text-zinc-400">Panitia & Juri terdaftar</div>
            </div>

            <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4 shadow-sm dark:border-blue-900/40 dark:bg-zinc-900">
                <span class="text-xs font-medium text-blue-700 dark:text-blue-400">Panitia Pelaksana</span>
                <div class="mt-2 text-2xl font-bold text-blue-950 dark:text-blue-200">{{ $stats['committee'] }}</div>
                <div class="text-[11px] text-blue-600/70 dark:text-blue-400/70">Operasional & Lapangan</div>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-zinc-900">
                <span class="text-xs font-medium text-amber-700 dark:text-amber-400">Dewan Juri</span>
                <div class="mt-2 text-2xl font-bold text-amber-950 dark:text-amber-200">{{ $stats['judge'] }}</div>
                <div class="text-[11px] text-amber-600/70 dark:text-amber-400/70">Penilaian Pos & Lomba</div>
            </div>

            <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-4 shadow-sm dark:border-purple-900/40 dark:bg-zinc-900">
                <span class="text-xs font-medium text-purple-700 dark:text-purple-400">Peran Ganda (Dual)</span>
                <div class="mt-2 text-2xl font-bold text-purple-950 dark:text-purple-200">{{ $stats['dual'] }}</div>
                <div class="text-[11px] text-purple-600/70 dark:text-purple-400/70">Panitia merangkap Juri</div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.officials.index', ['role' => 'all', 'search' => $search]) }}"
                   class="rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.officials.index', ['role' => 'committee', 'search' => $search]) }}"
                   class="rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'committee' ? 'bg-blue-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                    Panitia ({{ $stats['committee'] }})
                </a>
                <a href="{{ route('admin.officials.index', ['role' => 'judge', 'search' => $search]) }}"
                   class="rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'judge' ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                    Dewan Juri ({{ $stats['judge'] }})
                </a>
                <a href="{{ route('admin.officials.index', ['role' => 'dual', 'search' => $search]) }}"
                   class="rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'dual' ? 'bg-purple-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                    Peran Ganda ({{ $stats['dual'] }})
                </a>
            </div>

            <form method="GET" action="{{ route('admin.officials.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="role" value="{{ $roleFilter }}">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama, NTA, instansi..."
                    class="w-full sm:w-64 rounded-xl border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                <button type="submit" class="rounded-xl bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200">
                    Cari
                </button>
            </form>
        </div>

        {{-- Table List of Officials --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-zinc-200 bg-zinc-50/70 text-zinc-500 font-semibold dark:border-zinc-800 dark:bg-zinc-800/40">
                        <tr>
                            <th class="py-3 px-4">Aparatur</th>
                            <th class="py-3 px-4">Peran & Penugasan</th>
                            <th class="py-3 px-4">Instansi & Jabatan</th>
                            <th class="py-3 px-4">Kontak & NTA</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">ID Card Digital</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($officials as $official)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        @if($official->avatar_path)
                                            <img src="{{ asset('storage/' . $official->avatar_path) }}" alt="{{ $official->name }}" class="size-9 rounded-full object-cover border border-zinc-200 dark:border-zinc-700">
                                        @else
                                            <div class="flex size-9 items-center justify-center rounded-full bg-zinc-100 font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $official->initials() }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $official->name }}</div>
                                            <div class="text-[11px] text-zinc-400">{{ $official->email }}</div>
                                            @if($official->id_number)
                                                <span class="inline-block mt-1 font-mono text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800/50">{{ $official->id_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($official->roles as $role)
                                            @if($role->name === 'committee')
                                                <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                                    Panitia
                                                </span>
                                            @elseif($role->name === 'judge')
                                                <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                    Dewan Juri
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $official->institution ?: 'Kwarcab Padangsidimpuan' }}</div>
                                    <div class="text-[11px] text-zinc-400">{{ $official->position ?: 'Aparatur Resmi' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-mono text-zinc-700 dark:text-zinc-300">{{ $official->phone_number ?: '-' }}</div>
                                    <div class="text-[11px] text-zinc-400">NTA: {{ $official->scout_id ?: '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($official->is_active)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-zinc-200 px-2.5 py-0.5 text-[10px] font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                                            Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('digital-cards.staff', $official->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-amber-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-400">
                                        <flux:icon name="qr-code" class="size-3.5" />
                                        Cetak ID Card
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.officials.edit', $official->id) }}" class="font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-400">
                                    Belum ada aparatur yang terdaftar dengan kriteria ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($officials->hasPages())
                <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                    {{ $officials->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
