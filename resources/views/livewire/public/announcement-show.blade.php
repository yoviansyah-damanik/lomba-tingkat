@php
    $theme = match ($announcement->category) {
        'urgent' => ['icon' => 'exclamation-triangle', 'label' => 'Penting', 'band' => 'from-red-700 via-red-600 to-rose-500', 'glow' => 'bg-red-400/30'],
        'technical' => ['icon' => 'cog-6-tooth', 'label' => 'Teknis', 'band' => 'from-blue-800 via-blue-700 to-sky-600', 'glow' => 'bg-sky-300/30'],
        'schedule' => ['icon' => 'calendar-days', 'label' => 'Jadwal', 'band' => 'from-emerald-800 via-emerald-700 to-teal-600', 'glow' => 'bg-emerald-300/30'],
        default => ['icon' => 'megaphone', 'label' => ucfirst($announcement->category ?: 'Umum'), 'band' => 'from-amber-700 via-amber-600 to-amber-500', 'glow' => 'bg-amber-300/30'],
    };
    $readingMinutes = max(1, (int) ceil(str_word_count(strip_tags($announcement->content)) / 200));
    $files = $announcement->attachmentFiles();
    $shareText = rawurlencode($announcement->title.' — '.route('announcements.show', $announcement->slug));
@endphp
<div>
    {{-- Banner judul --}}
    <header class="relative isolate overflow-hidden bg-linear-to-br {{ $theme['band'] }} text-white">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -top-24 -right-16 size-96 rounded-full {{ $theme['glow'] }} blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.12)_1px,transparent_0)] bg-size-[24px_24px] mask-[linear-gradient(to_bottom,black,transparent_90%)]"></div>
        </div>

        <div class="mx-auto max-w-4xl px-4 pt-5 pb-12 sm:px-6 sm:pt-6 sm:pb-14 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-white/75">
                <a href="{{ route('home') }}" wire:navigate class="hover:text-white hover:underline">Beranda</a>
                <span>&rsaquo;</span>
                <a href="{{ route('announcements') }}" wire:navigate class="hover:text-white hover:underline">Pengumuman</a>
            </nav>

            <div class="mt-4 flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                    <flux:icon :name="$theme['icon']" class="size-4" />
                    {{ $theme['label'] }}
                </span>
                @if ($announcement->category === 'urgent')
                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-red-700">Baca Segera</span>
                @endif
            </div>

            <h1 class="mt-3 text-2xl font-black leading-tight tracking-tight sm:text-4xl">
                {{ $announcement->title }}
            </h1>

            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-white/80">
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon name="calendar" class="size-4" />
                    {{ $announcement->published_at?->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}, {{ $announcement->published_at?->timezone('Asia/Jakarta')->format('H:i') }} WIB
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon name="book-open" class="size-4" />
                    {{ $readingMinutes }} menit baca
                </span>
                @if ($files)
                    <span class="inline-flex items-center gap-1.5 font-semibold text-white">
                        <flux:icon name="paper-clip" class="size-4" />
                        {{ count($files) }} berkas unduhan
                    </span>
                @endif
            </div>
        </div>
    </header>

    <div class="mx-auto -mt-8 max-w-4xl px-4 pb-12 sm:px-6 lg:px-8">
        <article class="relative z-10 rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl shadow-zinc-900/5 dark:border-zinc-800 dark:bg-zinc-900 sm:p-10">
            {{-- Penerbit --}}
            <div class="flex items-center gap-3 border-b border-zinc-200/80 pb-5 dark:border-zinc-800">
                <img src="{{ asset('logo.png') }}" alt="Logo Kwarcab" class="size-11 shrink-0 object-contain">
                <div class="min-w-0 text-xs">
                    <span class="block font-bold text-zinc-900 dark:text-zinc-100">Bidang Humas dan Informatika</span>
                    <span class="text-zinc-500 dark:text-zinc-400">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</span>
                </div>
            </div>

            {{-- Isi pengumuman: paragraf, daftar berbutir, dan daftar bernomor dipisahkan agar mudah dibaca --}}
            @php
                $isBullet = fn ($l) => (bool) preg_match('/^[-•*]\s+/u', $l);
                $isNumber = fn ($l) => (bool) preg_match('/^\d+[.)]\s+/', $l);
                $isHeading = fn ($l) => mb_strlen($l) <= 70 && str_ends_with($l, ':');

                // Satu blok teks dapat memuat subjudul ("Judul:") diikuti daftar; keduanya dipecah menjadi elemen terpisah
                $blocks = collect(preg_split("/\n{2,}/", trim(str_replace("\r\n", "\n", (string) $announcement->content))))
                    ->flatMap(function (string $block) use ($isBullet, $isNumber, $isHeading) {
                        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), 'strlen'));
                        $out = [];

                        if (count($lines) > 1 && $isHeading($lines[0]) && collect(array_slice($lines, 1))->every(fn ($l) => $isBullet($l) || $isNumber($l))) {
                            $out[] = ['type' => 'h', 'text' => rtrim(array_shift($lines), ':')];
                        }

                        $out[] = match (true) {
                            $lines === [] => null,
                            collect($lines)->every($isBullet) => ['type' => 'ul', 'items' => array_map(fn ($l) => preg_replace('/^[-•*]\s+/u', '', $l), $lines)],
                            collect($lines)->every($isNumber) => ['type' => 'ol', 'items' => array_map(fn ($l) => preg_replace('/^\d+[.)]\s+/', '', $l), $lines)],
                            count($lines) === 1 && $isHeading($lines[0]) => ['type' => 'h', 'text' => rtrim($lines[0], ':')],
                            default => ['type' => 'p', 'text' => implode("\n", $lines)],
                        };

                        return $out;
                    })->filter()->values();
            @endphp

            <div class="mx-auto mt-6 max-w-[68ch] space-y-5 text-base leading-8 text-zinc-700 dark:text-zinc-300">
                @if ($announcement->summary)
                    <p class="rounded-r-xl border-l-4 border-amber-500 bg-amber-50/70 py-3 pl-4 pr-3 text-lg font-medium leading-8 text-zinc-800 dark:bg-amber-950/30 dark:text-zinc-100">{{ $announcement->summary }}</p>
                @endif

                @foreach ($blocks as $block)
                    @switch($block['type'])
                        @case('h')
                            <h2 class="pt-2 text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $block['text'] }}</h2>
                            @break
                        @case('ul')
                            <ul class="space-y-2">
                                @foreach ($block['items'] as $item)
                                    <li class="flex gap-3"><span class="mt-3 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                            @break
                        @case('ol')
                            <ol class="space-y-2">
                                @foreach ($block['items'] as $item)
                                    <li class="flex gap-3"><span class="mt-1.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">{{ $loop->iteration }}</span><span>{{ $item }}</span></li>
                                @endforeach
                            </ol>
                            @break
                        @default
                            <p class="whitespace-pre-line">{{ $block['text'] }}</p>
                    @endswitch
                @endforeach
            </div>

            {{-- Berkas unduhan --}}
            @if ($files)
                <section class="mt-10" aria-label="Berkas unduhan">
                    <h2 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500">
                        <flux:icon name="paper-clip" class="size-4 text-amber-600" />
                        Berkas Unduhan ({{ count($files) }})
                    </h2>
                    <ul class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($files as $index => $file)
                            @php $extension = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)); @endphp
                            <li class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 transition hover:border-amber-400 hover:bg-amber-50/60 dark:border-zinc-800 dark:bg-zinc-800/40 dark:hover:border-amber-500/40">
                                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-[11px] font-extrabold tracking-wide text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                    {{ \Illuminate\Support\Str::limit($extension, 4, '') ?: 'FILE' }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <span class="block truncate text-xs font-semibold text-zinc-900 dark:text-zinc-100" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                                    <span class="text-[11px] text-zinc-500">{{ \App\Models\Announcement::formatFileSize((int) $file['size']) }}</span>
                                </div>
                                <flux:button href="{{ route('announcements.download', [$announcement->slug, $index]) }}" size="sm" variant="primary" icon="arrow-down-tray" class="shrink-0">
                                    Unduh
                                </flux:button>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Aksi --}}
            <div class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-zinc-200/80 pt-5 dark:border-zinc-800">
                <flux:button href="{{ route('announcements') }}" wire:navigate variant="subtle" icon="arrow-left">Semua Pengumuman</flux:button>

                <div class="flex items-center gap-2" x-data="{ copied: false }">
                    <flux:button
                        size="sm"
                        icon="link"
                        x-on:click="navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                    >
                        <span x-text="copied ? 'Tertaut!' : 'Salin Tautan'">Salin Tautan</span>
                    </flux:button>
                    <flux:button href="https://wa.me/?text={{ $shareText }}" target="_blank" rel="noopener" size="sm" icon="paper-airplane">
                        Bagikan WhatsApp
                    </flux:button>
                </div>
            </div>
        </article>

        {{-- Pengumuman lainnya --}}
        @if ($recentAnnouncements->count())
            <section class="mt-12">
                <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500">Pengumuman Lainnya</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach ($recentAnnouncements as $recent)
                        <a href="{{ route('announcements.show', $recent->slug) }}" wire:navigate
                            class="group flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500/40">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $recent->categoryBadge() }}">{{ ucfirst($recent->category) }}</span>
                                    <span class="text-[10px] text-zinc-400">{{ $recent->published_at?->translatedFormat('d M Y') }}</span>
                                </div>
                                <h4 class="mt-2 line-clamp-3 text-sm font-bold text-zinc-900 group-hover:text-amber-700 dark:text-zinc-100 dark:group-hover:text-amber-400">
                                    {{ $recent->title }}
                                </h4>
                            </div>
                            <span class="mt-3 text-[11px] font-semibold text-amber-700 dark:text-amber-400">Baca &rarr;</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
