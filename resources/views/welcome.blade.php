<x-layouts::public title="Beranda Resmi LT III Kwarcab Padangsidimpuan">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden border-b border-amber-200/40 bg-gradient-to-b from-amber-500/10 via-amber-600/5 to-transparent py-12 sm:py-20 dark:border-amber-500/10 dark:from-amber-950/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-8 lg:grid-cols-12 lg:gap-12">
                {{-- Left Content --}}
                <div class="space-y-6 lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-100/60 px-3 py-1 text-xs font-semibold text-amber-800 backdrop-blur-sm dark:border-amber-400/20 dark:bg-amber-950/60 dark:text-amber-300">
                        <span class="size-2 rounded-full bg-amber-500 animate-ping"></span>
                        Aplikasi Resmi Penyelenggaraan Kegiatan
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-zinc-950 sm:text-5xl lg:text-5xl dark:text-zinc-50 leading-tight">
                        Lomba Tingkat III<br>
                        Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan<br>
                        Tahun 2026
                    </h1>

                    <p class="text-sm font-medium text-amber-800 dark:text-amber-400 sm:text-base">
                        &ldquo;{{ $event->theme ?? 'Tangkas, Berkarakter, Bersaudara, dan Berprestasi Menuju Generasi Indonesia Emas' }}&rdquo;
                    </p>

                    <p class="text-sm text-zinc-600 dark:text-zinc-300 sm:text-base leading-relaxed">
                        {!! nl2br(e($event?->description ?: 'Ajang prestasi regu Penggalang SMP/MTs se-Kota Padangsidimpuan. Terintegrasi dengan verifikasi kontingen, penerbitan kartu digital QR Code, validasi presensi berbasis waktu server resmi WIB, penilaian kompetisi blind scoring, dan siaran langsung klasemen perolehan prestasi.')) !!}
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        @guest
                            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-600/25 transition-all hover:brightness-105">
                                <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                                <span>Masuk ke Sistem</span>
                            </a>
                        @endguest

                        <a href="{{ route('competitions') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-800 shadow-sm transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            <flux:icon name="document-text" class="size-4 text-amber-600" />
                            Mata Lomba & Juknis
                        </a>

                        <a href="{{ route('schedules') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-800 shadow-sm transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            Jadwal Kegiatan
                        </a>

                        <a href="{{ route('participants') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-800 shadow-sm transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            <flux:icon name="user-group" class="size-4 text-amber-600" />
                            Peserta Kontingen
                        </a>

                        <a href="{{ route('guide') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 px-3 py-2">
                            Panduan &rarr;
                        </a>
                    </div>
                </div>

                {{-- Right Card / Event Highlights --}}
                <div class="lg:col-span-5">
                    <div class="rounded-2xl border border-amber-300/60 bg-gradient-to-br from-white to-amber-50/50 p-6 shadow-xl shadow-amber-900/5 dark:border-amber-500/20 dark:from-zinc-900 dark:to-zinc-900/90">
                        <div class="flex items-center justify-between border-b border-zinc-200/80 pb-4 dark:border-zinc-800">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Penyelenggaraan</span>
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">LT III Kota Padangsidimpuan</h3>
                            </div>
                            <span class="inline-flex items-center rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                Aktif
                            </span>
                        </div>

                        <div class="mt-4 space-y-3.5 text-xs sm:text-sm">
                            <div class="flex items-start gap-3">
                                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="block font-medium text-zinc-500 dark:text-zinc-400">Lokasi Perkemahan</span>
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $event->location ?? 'Bumi Perkemahan Kwarcab Padangsidimpuan' }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <span class="block font-medium text-zinc-500 dark:text-zinc-400">Waktu Pelaksanaan</span>
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $event?->start_date ? \Carbon\Carbon::parse($event->start_date)->locale('id')->isoFormat('D MMMM Y') : '15 November 2026' }} s/d {{ $event?->end_date ? \Carbon\Carbon::parse($event->end_date)->locale('id')->isoFormat('D MMMM Y') : '18 November 2026' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <span class="block font-medium text-zinc-500 dark:text-zinc-400">Otoritas Penyelenggara</span>
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</span>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Metrics --}}
                        <div class="mt-6 grid grid-cols-3 gap-2 border-t border-zinc-200/80 pt-4 text-center dark:border-zinc-800">
                            <div class="rounded-xl bg-zinc-100/70 p-2.5 dark:bg-zinc-800/60">
                                <span class="block text-xl font-bold text-amber-700 dark:text-amber-400">{{ $stats['total_competitions'] ?? 8 }}</span>
                                <span class="text-[11px] text-zinc-500">Mata Lomba</span>
                            </div>
                            <div class="rounded-xl bg-zinc-100/70 p-2.5 dark:bg-zinc-800/60">
                                <span class="block text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $stats['total_schedules'] ?? 5 }}</span>
                                <span class="text-[11px] text-zinc-500">Sesi Jadwal</span>
                            </div>
                            <div class="rounded-xl bg-zinc-100/70 p-2.5 dark:bg-zinc-800/60">
                                <span class="block text-xl font-bold text-blue-700 dark:text-blue-400">3</span>
                                <span class="text-[11px] text-zinc-500">Peringkat Prestasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Pengumuman Terbaru --}}
    <section class="border-b border-zinc-200/80 bg-white py-12 dark:border-zinc-800 dark:bg-zinc-900/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Informasi Resmi</span>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Pengumuman Terkini</h2>
                </div>
                <a href="{{ route('announcements') }}" wire:navigate class="text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 sm:text-sm">
                    Lihat Semua Pengumuman &rarr;
                </a>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                @forelse($latestAnnouncements as $announcement)
                    <article class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-zinc-50/50 p-5 shadow-sm transition-all hover:border-amber-400/60 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500/30">
                        <div>
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 font-medium {{ $announcement->categoryBadge() }}">
                                    {{ ucfirst($announcement->category) }}
                                </span>
                                <span>{{ $announcement->published_at ? \Carbon\Carbon::parse($announcement->published_at)->locale('id')->isoFormat('D MMMM Y') : '-' }}</span>
                            </div>
                            <h3 class="mt-3 text-base font-bold text-zinc-900 hover:text-amber-700 dark:text-zinc-100 dark:hover:text-amber-400">
                                <a href="{{ route('announcements.show', $announcement->slug) }}" wire:navigate>
                                    {{ $announcement->title }}
                                </a>
                            </h3>
                            <p class="mt-2 text-xs text-zinc-600 line-clamp-3 dark:text-zinc-400">
                                {{ $announcement->summary ?? Str::limit(strip_tags($announcement->content), 120) }}
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-zinc-200/60 dark:border-zinc-800">
                            <a href="{{ route('announcements.show', $announcement->slug) }}" wire:navigate class="text-xs font-semibold text-amber-700 hover:underline dark:text-amber-400">
                                Baca Selengkapnya &rarr;
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-3 rounded-xl border border-dashed border-zinc-300 p-8 text-center text-sm text-zinc-500 dark:border-zinc-800">
                        Belum ada pengumuman resmi yang dipublikasikan.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Cabang Mata Lomba Pilihan --}}
    <section class="border-b border-zinc-200/80 bg-zinc-50 py-12 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Kompetisi Prestasi</span>
                <h2 class="text-2xl font-bold text-zinc-900 sm:text-3xl dark:text-zinc-100">Mata Lomba & Juknis Kegiatan</h2>
                <p class="mt-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                    Setiap cabang mata lomba dinilai berdasarkan rubrik juknis dengan skala objektif dan penerapan penyamaran identitas (*blind scoring*).
                </p>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($competitions as $competition)
                    <div class="rounded-xl border border-zinc-200/80 bg-white p-5 shadow-sm transition-all hover:border-amber-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">
                                {{ $competition->code }}
                            </span>
                            <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                {{ $competition->categoryLabel() }}
                            </span>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $competition->title }}
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                            {{ $competition->description }}
                        </p>
                        <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs">
                            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $competition->participantRangeLabel() }}</span>
                            <a href="{{ route('competitions') }}" wire:navigate class="font-semibold text-amber-700 hover:underline dark:text-amber-400">
                                Juknis &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('competitions') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-5 py-2.5 text-xs font-semibold text-amber-900 transition-colors hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
                    Buka Seluruh Katalog Mata Lomba ({{ $stats['total_competitions'] }})
                </a>
            </div>
        </div>
    </section>

    {{-- Jadwal & Arena Kegiatan Terdekat --}}
    @if(isset($upcomingSchedules) && $upcomingSchedules->count())
    <section class="border-b border-zinc-200/80 bg-white py-12 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Waktu & Lokasi</span>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Jadwal Sesi Kegiatan Terdekat</h2>
                </div>
                <a href="{{ route('schedules') }}" wire:navigate class="text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 sm:text-sm">
                    Lihat Seluruh Jadwal ({{ $stats['total_schedules'] ?? $upcomingSchedules->count() }}) &rarr;
                </a>
            </div>

            <div class="mt-6 space-y-3">
                @foreach($upcomingSchedules as $sched)
                    <div class="flex flex-col justify-between gap-3 rounded-xl border border-zinc-200/80 bg-zinc-50/60 p-4 transition hover:border-amber-400 sm:flex-row sm:items-center dark:border-zinc-800 dark:bg-zinc-900/60">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">
                                    {{ $sched->competition?->code }}
                                </span>
                                <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $sched->competition?->title }}
                                </span>
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $sched->participantTypeBadgeClasses() }}">
                                    {{ $sched->participantTypeLabel() }}
                                </span>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $sched->session_name }}
                            </h3>
                            <p class="text-xs text-zinc-500">
                                Lokasi: {{ $sched->arena_location }} &bull; Waktu: {{ \Carbon\Carbon::parse($sched->start_time)->locale('id')->isoFormat('dddd, D MMMM Y [pukul] HH:mm') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }} WIB
                            </p>
                        </div>
                        <div class="flex items-center gap-2 self-start sm:self-center">
                            <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[11px] font-semibold {{ $sched->statusBadge() }}">
                                {{ $sched->statusLabel() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @php
        $paymentConfig = \App\Models\Event::activePaymentConfig();
    @endphp

    {{-- Penjelasan Biaya Pendaftaran Kontingen per Regu --}}
    <section class="border-b border-zinc-200/80 bg-white py-14 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 rounded-3xl border border-amber-300/80 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/40 p-8 sm:p-10 shadow-lg shadow-amber-900/5 dark:border-amber-500/20 dark:from-zinc-900 dark:via-zinc-900 dark:to-amber-950/30">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-100/80 px-3 py-1 text-xs font-bold text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/60 dark:text-amber-300 mb-3">
                        <flux:icon name="banknotes" class="size-3.5" />
                        Ketentuan Biaya Pendaftaran
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Biaya Kontingen Dihitung Per Regu
                    </h2>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Setiap pangkalan Gugus Depan yang berpartisipasi dalam LT III Kwartir Cabang Kota Padangsidimpuan dikenakan biaya pendaftaran resmi per regu (Putra atau Putri) yang didaftarkan.
                    </p>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-zinc-700 dark:text-zinc-300">
                        <div class="flex items-center gap-2.5 rounded-xl border border-amber-200/80 bg-white/80 p-3 dark:border-zinc-800 dark:bg-zinc-800/60">
                            <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span>ID Card Kontingen format B2 bertoken QR</span>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-xl border border-amber-200/80 bg-white/80 p-3 dark:border-zinc-800 dark:bg-zinc-800/60">
                            <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span>Piagam &amp; TISKA Resmi Kwarcab</span>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-xl border border-amber-200/80 bg-white/80 p-3 dark:border-zinc-800 dark:bg-zinc-800/60">
                            <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span>Kit Perkemahan &amp; Buku Petunjuk Teknis</span>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-xl border border-amber-200/80 bg-white/80 p-3 dark:border-zinc-800 dark:bg-zinc-800/60">
                            <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span>Akses Penilaian &amp; Leaderboard Live</span>
                        </div>
                    </div>
                </div>

                {{-- Price & Bank Account Card --}}
                <div class="w-full lg:w-96 shrink-0 rounded-2xl border border-amber-300 bg-white p-6 shadow-md dark:border-zinc-800 dark:bg-zinc-900 text-center">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Tarif Registrasi</span>
                    <div class="mt-2 flex items-baseline justify-center gap-1">
                        <span class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">{{ $paymentConfig['formatted_fee_per_patrol'] }}</span>
                        <span class="text-xs text-zinc-500">/ regu</span>
                    </div>
                    <p class="mt-1 text-[11px] text-zinc-500">
                        (Contoh: 2 Regu Putra &amp; Putri = Rp {{ number_format($paymentConfig['fee_per_patrol'] * 2, 0, ',', '.') }})
                    </p>

                    <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50/70 p-3 text-left text-xs dark:border-zinc-800 dark:bg-zinc-800/40">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block mb-1">Rekening Pembayaran Resmi:</span>
                        <div class="font-semibold text-zinc-900 dark:text-zinc-100 text-xs">{{ $paymentConfig['bank_name'] }}</div>
                        <div class="font-mono font-bold text-amber-800 dark:text-amber-400 text-sm tracking-wide">{{ $paymentConfig['account_number'] }}</div>
                        <div class="text-[11px] text-zinc-600 dark:text-zinc-400 leading-tight mt-0.5">{{ $paymentConfig['account_holder'] }}</div>
                    </div>

                    <div class="mt-5">
                        <flux:button href="{{ route('register.scout-group') }}" variant="primary" icon="arrow-right" class="w-full justify-center">
                            Daftarkan Pangkalan
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Alur Pendaftaran Kontingen --}}
    <section class="border-b border-zinc-200/80 bg-zinc-50/50 py-12 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-xl mx-auto">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Alur Sistem</span>
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Tahapan Pendaftaran Kontingen</h2>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative rounded-xl border border-zinc-200 bg-zinc-50/60 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-600 text-white font-bold text-xs">1</div>
                    <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Registrasi Gudep</h3>
                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Penanggung jawab pangkalan mengisi profil sekolah dan nomor Gugus Depan.</p>
                </div>

                <div class="relative rounded-xl border border-zinc-200 bg-zinc-50/60 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-600 text-white font-bold text-xs">2</div>
                    <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Input Regu &amp; Peserta</h3>
                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Membuat regu Putra/Putri, memasukkan biodata peserta dan Bindamping.</p>
                </div>

                <div class="relative rounded-xl border border-zinc-200 bg-zinc-50/60 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-600 text-white font-bold text-xs">3</div>
                    <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Pembayaran &amp; Berkas</h3>
                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Membayar biaya per regu dan mengunggah Bukti Bayar serta Surat Tugas.</p>
                </div>

                <div class="relative rounded-xl border border-zinc-200 bg-zinc-50/60 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-600 text-white font-bold text-xs">4</div>
                    <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Verifikasi &amp; Kartu QR</h3>
                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Setelah disetujui, kartu digital bertoken aman terbit dan siap diunduh/dicetak.</p>
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>
