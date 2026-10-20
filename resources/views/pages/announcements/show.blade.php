<x-layouts::public :title="$announcement->title">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="flex text-xs text-zinc-500 mb-4">
            <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
            <span class="mx-2">&rsaquo;</span>
            <a href="{{ route('announcements') }}" wire:navigate class="hover:underline">Pengumuman</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200 truncate">{{ $announcement->title }}</span>
        </nav>

        <article class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            {{-- Header --}}
            <div class="border-b border-zinc-200/80 pb-6 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $announcement->categoryBadge() }}">
                        {{ ucfirst($announcement->category) }}
                    </span>
                    <span class="text-xs text-zinc-500">
                        Dipublikasikan: {{ $announcement->published_at?->translatedFormat('d F Y, H:i') }} WIB
                    </span>
                </div>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                    {{ $announcement->title }}
                </h1>
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Otoritas: <span class="font-medium text-zinc-700 dark:text-zinc-300">Bidang Humas dan Informatika Kwarcab Kota Padangsidimpuan</span>
                </p>
            </div>

            {{-- Content Body --}}
            <div class="prose prose-zinc mt-6 max-w-none text-sm sm:text-base leading-relaxed text-zinc-700 dark:prose-invert dark:text-zinc-300 whitespace-pre-line">
                {{ $announcement->content }}
            </div>

            {{-- Attachment if any --}}
            @if($announcement->attachment_path)
                <div class="mt-8 rounded-xl border border-amber-300/60 bg-amber-50/60 p-4 dark:border-amber-500/20 dark:bg-amber-950/40">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-medium text-amber-900 dark:text-amber-200">
                            <svg class="size-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>Lampiran Dokumen Resmi</span>
                        </div>
                        <a href="{{ asset($announcement->attachment_path) }}" target="_blank" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                            Unduh Lampiran
                        </a>
                    </div>
                </div>
            @endif
        </article>

        {{-- Recent Announcements --}}
        @if($recentAnnouncements->count())
            <div class="mt-10">
                <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500">Pengumuman Terkait Lainnya</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach($recentAnnouncements as $recent)
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="text-[10px] text-zinc-400">{{ $recent->published_at?->translatedFormat('d M Y') }}</span>
                            <h4 class="mt-1 text-xs font-bold text-zinc-900 hover:text-amber-700 dark:text-zinc-100">
                                <a href="{{ route('announcements.show', $recent->slug) }}" wire:navigate>
                                    {{ $recent->title }}
                                </a>
                            </h4>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::public>
