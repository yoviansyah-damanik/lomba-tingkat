<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8">
    {{-- Page Header --}}
    <div
        class="relative overflow-hidden rounded-3xl bg-linear-to-br from-amber-950 via-zinc-950 to-zinc-900 p-6 sm:p-10 text-white shadow-xl border border-amber-900/40">
        <div class="relative z-10 max-w-3xl space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-amber-400/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300 backdrop-blur-sm">
                    <flux:icon name="academic-cap" class="size-3.5" />
                    Dokumen Penghargaan Resmi
                </span>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold backdrop-blur-sm {{ $isPublished ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300' : 'border-zinc-500/30 bg-zinc-800/40 text-zinc-300' }}">
                    <span
                        class="size-2 rounded-full {{ $isPublished ? 'bg-emerald-400 animate-pulse' : 'bg-zinc-400' }}"></span>
                    Penerbitan {{ $isPublished ? 'Telah Dibuka (Aktif)' : 'Belum Dibuka' }}
                </span>
            </div>

            <h1 class="text-3xl font-black tracking-tight sm:text-5xl text-zinc-100">
                Penerbitan E-Sertifikat Resmi
            </h1>
            <p class="text-sm sm:text-base text-zinc-300 leading-relaxed">
                Unduh dan verifikasi piagam sertifikat keikutsertaan Lomba Tingkat III (LT-III) Kwartir Cabang Gerakan
                Pramuka Kota Padangsidimpuan untuk Peserta, Pembina Pendamping, Panitia Pelaksana, dan Dewan Juri.
            </p>
        </div>
    </div>

    @if (!$isPublished)
        {{-- Banner Belum Dibuka --}}
        <div
            class="rounded-3xl border border-zinc-200 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-12 space-y-4">
            <div
                class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                <flux:icon name="clock" class="size-8" />
            </div>
            <div class="max-w-md mx-auto space-y-2">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Penerbitan E-Sertifikat Belum Dibuka</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Panitia pelaksana kegiatan LT III sedang memproses verifikasi berkas dan penyiapan berkas tanda
                    kelulusan. Pemberitahuan akan ditampilkan di halaman ini saat sertifikat resmi diterbitkan.
                </p>
            </div>
            <div class="pt-2">
                <flux:button href="{{ route('home') }}" wire:navigate icon="arrow-left" variant="outline">
                    Kembali ke Beranda
                </flux:button>
            </div>
        </div>
    @else
        {{-- Jika Pengguna Login dan Memiliki Sertifikat Pribadi --}}
        @if (auth()->check() && !empty($myCertificates))
            <div
                class="rounded-3xl border border-amber-500/30 bg-amber-50/50 p-6 shadow-sm dark:border-amber-500/20 dark:bg-amber-950/20 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-xs">
                            <flux:icon name="sparkles" class="size-6" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">E-Sertifikat Pribadi Anda
                                Telah Terbit</h2>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">
                                Sertifikat diterbitkan atas nama <span
                                    class="font-bold text-zinc-800 dark:text-zinc-200">{{ auth()->user()->name }}</span>.
                            </p>
                        </div>
                    </div>

                    <div>
                        <flux:button href="{{ route('certificates.my') }}" icon="arrow-down-tray" variant="primary"
                            class="px-5">
                            Unduh E-Sertifikat Saya
                        </flux:button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
                    @foreach ($myCertificates as $c)
                        <div
                            class="rounded-xl border border-amber-200/70 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900 space-y-1 text-xs">
                            <span
                                class="inline-block rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                {{ $c['role_label'] }}
                            </span>
                            <p class="font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $c['full_name'] }}</p>
                            <p class="text-[11px] text-zinc-500 truncate">{{ $c['scout_id'] }}</p>
                            <p class="text-[11px] font-mono text-zinc-400 truncate">{{ $c['certificate_number'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Jika Akun Pangkalan Gudep Login: Roster Kontingen --}}
        @if (auth()->check() && auth()->user()->isScoutGroup() && !empty($scoutGroupRoster))
            <div
                class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <flux:icon name="building-library" class="size-5 text-amber-500" />
                            Sertifikat Kontingen Pangkalan Anda
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Daftar seluruh peserta dan pembina pendamping terverifikasi dari gugus depan Anda.
                        </p>
                    </div>
                    <span
                        class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-400 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                        {{ count($scoutGroupRoster) }} Sertifikat Siap Cetak
                    </span>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($scoutGroupRoster as $item)
                        <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-bold text-sm text-zinc-900 dark:text-zinc-100">{{ $item['data']['full_name'] }}</span>
                                    <span
                                        class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $item['category'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-500">{{ $item['data']['scout_id'] }} &bull;
                                    {{ $item['data']['institution'] }}</p>
                                <p class="text-[11px] font-mono text-zinc-400">
                                    {{ $item['data']['certificate_number'] }}</p>
                            </div>

                            <div>
                                <flux:button href="{{ $item['download_url'] }}" icon="arrow-down-tray" size="sm"
                                    variant="outline">
                                    Unduh Sertifikat
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pencarian & Verifikasi Publik --}}
        <div
            class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-6">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <flux:icon name="magnifying-glass" class="size-5 text-amber-500" />
                    Pencarian &amp; Verifikasi E-Sertifikat
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    Ketik Nama Lengkap, Nomor Tanda Anggota (NTA), atau Nomor ID (misal: P-102948) untuk memeriksa
                    keabsahan dan melihat data e-sertifikat.
                </p>
            </div>

            {{-- Filter & Search Form --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <flux:icon name="magnifying-glass"
                        class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-zinc-400" />
                    <input type="search" wire:model.live.debounce.300ms="search"
                        placeholder="Ketik minimal 3 huruf nama, NTA, atau nomor ID..."
                        class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 pl-10 pr-4 py-2.5 text-xs text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                </div>

                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                    @php
                        $filterOptions = [
                            'all' => 'Semua',
                            'participant' => 'Peserta',
                            'chaperone' => 'Bindamping',
                            'committee' => 'Panitia',
                            'judge' => 'Dewan Juri',
                        ];
                    @endphp
                    @foreach ($filterOptions as $fKey => $fLabel)
                        <button type="button" wire:click="$set('role', '{{ $fKey }}')"
                            class="rounded-xl px-3 py-2 text-xs font-semibold whitespace-nowrap transition-colors {{ $role === $fKey ? 'bg-amber-500 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                            {{ $fLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Hasil Pencarian --}}
            <div class="space-y-3">
                @if (strlen(trim($search)) >= 3)
                    @if (empty($results))
                        <div
                            class="rounded-2xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                            <flux:icon name="magnifying-glass" class="mx-auto size-8 text-zinc-400" />
                            <p class="mt-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tidak ada data
                                sertifikat yang cocok</p>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Pastikan nama atau nomor ID telah
                                terverifikasi resmi oleh panitia.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($results as $res)
                                @php $item = $res['data']; @endphp
                                <div
                                    class="rounded-2xl border border-zinc-200 bg-zinc-50/50 p-4 shadow-2xs dark:border-zinc-800 dark:bg-zinc-800/40 space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-0.5">
                                            <span
                                                class="inline-block rounded-md bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                                                {{ $item['role_label'] }}
                                            </span>
                                            <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $item['full_name'] }}</h3>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item['scout_id'] }}
                                            </p>
                                        </div>

                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <flux:icon name="check-badge" class="size-3" />
                                            Sah
                                        </span>
                                    </div>

                                    <div
                                        class="border-t border-zinc-200/60 pt-2.5 dark:border-zinc-700/60 space-y-1 text-xs">
                                        <p class="text-zinc-600 dark:text-zinc-300 text-[11px] truncate">
                                            <span class="text-zinc-400">Pangkalan:</span> {{ $item['institution'] }}
                                        </p>
                                        <p class="font-mono text-[11px] text-zinc-400 truncate">
                                            <span>Nomor:</span> {{ $item['certificate_number'] }}
                                        </p>
                                    </div>

                                    <div class="pt-1 flex items-center justify-between gap-2">
                                        <flux:button href="{{ $item['verification_url'] }}" size="xs"
                                            variant="ghost" icon="shield-check">
                                            Cek Keabsahan
                                        </flux:button>

                                        @auth
                                            <flux:button href="{{ $res['download_url'] }}" size="xs"
                                                variant="primary" icon="arrow-down-tray">
                                                Unduh Sertifikat
                                            </flux:button>
                                        @endauth
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div
                        class="rounded-2xl bg-zinc-50 p-6 text-center text-xs text-zinc-500 dark:bg-zinc-800/40 dark:text-zinc-400">
                        <flux:icon name="information-circle" class="mx-auto size-5 text-amber-500 mb-1" />
                        Silakan masukkan minimal 3 karakter pada kolom pencarian di atas untuk mencari sertifikat.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
