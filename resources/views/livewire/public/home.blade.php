<div>
        {{-- Hero Section --}}
        @php
            $tz = 'Asia/Jakarta';
            $start = $event?->start_date ? \Carbon\Carbon::parse($event->start_date, $tz)->startOfDay() : null;
            $end = $event?->end_date ? \Carbon\Carbon::parse($event->end_date, $tz)->endOfDay() : null;
            $now = now($tz);
            $phase = match (true) {
                ! $start || ! $end => ['label' => 'Segera Hadir', 'dot' => 'bg-amber-400'],
                $now->between($start, $end) => ['label' => 'Sedang Berlangsung', 'dot' => 'bg-emerald-400'],
                $now->lt($start) => ['label' => 'Dimulai '.$start->locale('id')->diffForHumans($now, ['parts' => 1]), 'dot' => 'bg-amber-400'],
                default => ['label' => 'Kegiatan Selesai', 'dot' => 'bg-zinc-400'],
            };
            $registrationOpen = (bool) $event?->isRegistrationOpen();
        @endphp
        <section class="relative isolate overflow-hidden bg-zinc-950 text-white">
            {{-- Dekorasi latar --}}
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-linear-to-br from-amber-950 via-zinc-950 to-zinc-950"></div>
                <div class="absolute -top-32 -left-24 size-120 rounded-full bg-amber-500/25 blur-3xl"></div>
                <div class="absolute -right-24 -bottom-40 size-136 rounded-full bg-amber-600/20 blur-3xl"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.07)_1px,transparent_0)] bg-size-[26px_26px] mask-[linear-gradient(to_bottom,black,transparent_85%)]"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
                <div class="grid items-center gap-12 lg:grid-cols-12">
                    {{-- Konten utama --}}
                    <div class="space-y-7 lg:col-span-7">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-amber-200 backdrop-blur-sm">
                                <span class="relative flex size-2">
                                    <span class="absolute inline-flex size-full animate-ping rounded-full {{ $phase['dot'] }} opacity-75"></span>
                                    <span class="relative inline-flex size-2 rounded-full {{ $phase['dot'] }}"></span>
                                </span>
                                {{ $phase['label'] }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold backdrop-blur-sm {{ $registrationOpen ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300' : 'border-white/15 bg-white/5 text-zinc-300' }}">
                                <flux:icon :name="$registrationOpen ? 'lock-open' : 'lock-closed'" class="size-3.5" />
                                Pendaftaran {{ $registrationOpen ? 'Dibuka' : 'Ditutup' }}
                            </span>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-400">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</p>
                            <h1 class="mt-3 text-4xl font-black leading-[1.05] tracking-tight sm:text-6xl">
                                Lomba Tingkat
                                <span class="bg-linear-to-r from-amber-300 via-amber-400 to-amber-600 bg-clip-text text-transparent">III</span>
                                <span class="block text-3xl font-extrabold text-zinc-100 sm:text-5xl">Tahun {{ $start?->year ?? 2026 }}</span>
                            </h1>
                        </div>

                        <p class="max-w-2xl border-l-2 border-amber-500 pl-4 text-base font-medium italic text-amber-100/90 sm:text-lg">
                            &ldquo;{{ $event->theme ?? 'Tangkas, Berkarakter, Bersaudara, dan Berprestasi Menuju Generasi Indonesia Emas' }}&rdquo;
                        </p>

                        <p class="max-w-2xl text-sm leading-relaxed text-zinc-300 sm:text-base">
                            {!! nl2br(e(\Illuminate\Support\Str::limit($event?->description ?: 'Ajang prestasi regu Penggalang SMP/MTs se-Kota Padangsidimpuan. Terintegrasi dengan verifikasi kontingen, penerbitan kartu digital QR Code, validasi presensi berbasis waktu server resmi WIB, penilaian kompetisi blind scoring, dan siaran langsung klasemen perolehan prestasi.', 280))) !!}
                        </p>

                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            @guest
                                <flux:button href="{{ route('login') }}" wire:navigate variant="primary" icon="arrow-right-end-on-rectangle" class="px-6">
                                    Masuk ke Sistem
                                </flux:button>
                                @if ($registrationOpen)
                                    <flux:button href="{{ route('register.scout-group') }}" wire:navigate icon="building-library">
                                        Daftarkan Pangkalan
                                    </flux:button>
                                @endif
                            @else
                                <flux:button href="{{ route('dashboard') }}" wire:navigate variant="primary" icon="squares-2x2" class="px-6">
                                    Buka Dasbor
                                </flux:button>
                            @endguest
                            <flux:button href="{{ route('competitions') }}" wire:navigate icon="document-text">Mata Lomba &amp; Juknis</flux:button>
                            <flux:button href="{{ route('schedules') }}" wire:navigate icon="calendar-days" variant="ghost" class="text-zinc-200!">Jadwal</flux:button>
                            <flux:button href="{{ route('guide') }}" wire:navigate icon="book-open-text" variant="ghost" class="text-zinc-200!">Panduan</flux:button>
                        </div>
                    </div>

                    {{-- Kartu sorotan kegiatan --}}
                    <div class="lg:col-span-5">
                        <div class="rounded-3xl border border-white/10 bg-white/6 p-6 shadow-2xl shadow-black/40 backdrop-blur-md sm:p-7">
                            <div class="flex items-center gap-4">
                                <img src="{{ asset('logo.png') }}" alt="Logo Kwarcab" class="size-16 shrink-0 object-contain drop-shadow-lg">
                                <div class="min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-amber-400">Penyelenggaraan</span>
                                    <h3 class="text-lg font-extrabold leading-tight text-white">LT III Kota Padangsidimpuan</h3>
                                </div>
                            </div>

                            {{-- Tanggal --}}
                            <div class="mt-6 flex items-stretch gap-3">
                                <div class="flex w-20 shrink-0 flex-col items-center justify-center rounded-2xl bg-linear-to-br from-amber-400 to-amber-600 py-3 text-zinc-950 shadow-lg shadow-amber-600/30">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">{{ $start?->locale('id')->isoFormat('MMM') ?? 'Nov' }}</span>
                                    <span class="text-3xl font-black leading-none">{{ $start?->format('d') ?? '14' }}</span>
                                    <span class="text-[10px] font-semibold">{{ $start?->year ?? 2026 }}</span>
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col justify-center text-sm">
                                    <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Waktu Pelaksanaan</span>
                                    <span class="font-bold text-white">
                                        {{ $start ? $start->locale('id')->isoFormat('D MMMM Y') : '14 November 2026' }}
                                    </span>
                                    <span class="text-xs text-zinc-300">s/d {{ $end ? $end->locale('id')->isoFormat('D MMMM Y') : '17 November 2026' }}</span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-start gap-3 rounded-2xl bg-white/5 p-3.5 text-sm">
                                <flux:icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-amber-400" />
                                <div class="min-w-0">
                                    <span class="block text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Lokasi Perkemahan</span>
                                    <span class="font-semibold text-white">{{ $event->location ?? 'Bumi Perkemahan Kwarcab Padangsidimpuan' }}</span>
                                </div>
                            </div>

                            {{-- Angka kunci --}}
                            <div class="mt-5 grid grid-cols-2 gap-2.5 text-center">
                                @foreach ([
                                    ['value' => $stats['total_competitions'] ?? 0, 'label' => 'Mata Lomba', 'icon' => 'trophy'],
                                    ['value' => $stats['total_schedules'] ?? 0, 'label' => 'Sesi Jadwal', 'icon' => 'calendar-days'],
                                    ['value' => $stats['total_groups'] ?? 0, 'label' => 'Gudep Terverifikasi', 'icon' => 'building-library'],
                                    ['value' => $stats['total_patrols'] ?? 0, 'label' => 'Regu Bertanding', 'icon' => 'user-group'],
                                ] as $metric)
                                    <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                                        <flux:icon :name="$metric['icon']" class="mx-auto size-4 text-amber-400" />
                                        <span class="mt-1 block text-2xl font-black text-white">{{ $metric['value'] }}</span>
                                        <span class="text-[11px] font-medium text-zinc-400">{{ $metric['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="h-1 bg-linear-to-r from-transparent via-amber-500 to-transparent"></div>
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
                            <flux:button href="{{ route('register.scout-group') }}" variant="primary" icon="arrow-right" class="w-full justify-center" wire:navigate>
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
</div>
