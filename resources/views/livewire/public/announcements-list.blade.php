<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Breadcrumb & Title --}}
    <x-heading-section
        class="mb-6"
        title="Pengumuman Resmi Panitia"
        description="Informasi resmi, surat edaran, dan pengumuman teknis LT III Kwarcab Padangsidimpuan."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Pengumuman</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-600 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                <span class="font-mono font-bold text-amber-700 dark:text-amber-400">{{ $announcements->total() }}</span>
                <span>Rilis Resmi</span>
            </div>
        </x-slot:actions>
    </x-heading-section>

    {{-- Filter & Search Form (Livewire 4) --}}
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <flux:icon name="magnifying-glass" class="absolute left-3.5 top-2.5 size-4 text-zinc-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari judul atau isi pengumuman..."
                class="w-full rounded-xl border border-zinc-200 bg-white pl-10 pr-4 py-2 text-xs sm:text-sm text-zinc-900 shadow-xs focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            />
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-zinc-400 hover:text-zinc-600">
                    &times;
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <select
                wire:model.live="category"
                class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-xs focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            >
                <option value="">Semua Kategori</option>
                <option value="general">Umum</option>
                <option value="technical">Teknis</option>
                <option value="schedule">Jadwal</option>
                <option value="urgent">Penting / Darurat</option>
            </select>

            @if($search || $category !== '')
                <button
                    type="button"
                    wire:click="$set('search', ''); $set('category', '');"
                    class="rounded-xl border border-zinc-300 px-3 py-2 text-xs sm:text-sm text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-400 cursor-pointer">
                    Atur Ulang
                </button>
            @endif
        </div>
    </div>

    {{-- Announcements Grid --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        @forelse($announcements as $announcement)
            <article class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:border-amber-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                <div>
                    <div class="flex items-center justify-between text-xs text-zinc-500">
                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 font-medium {{ $announcement->categoryBadge() }}">
                            {{ ucfirst($announcement->category) }}
                        </span>
                        <span>{{ $announcement->published_at?->translatedFormat('d M Y') }}</span>
                    </div>
                    <h2 class="mt-3 text-base font-bold text-zinc-900 hover:text-amber-700 dark:text-zinc-100 dark:hover:text-amber-400">
                        <a href="{{ route('announcements.show', $announcement->slug) }}" wire:navigate>
                            {{ $announcement->title }}
                        </a>
                    </h2>
                    <p class="mt-2 text-xs text-zinc-600 line-clamp-3 dark:text-zinc-400 leading-relaxed">
                        {{ $announcement->summary ?? Str::limit(strip_tags($announcement->content), 120) }}
                    </p>
                    @if($announcement->attachmentFiles())
                        <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                            <flux:icon name="paper-clip" class="size-3" />
                            {{ count($announcement->attachmentFiles()) }} berkas unduhan
                        </span>
                    @endif
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <a href="{{ route('announcements.show', $announcement->slug) }}" wire:navigate class="text-xs font-semibold text-amber-700 hover:underline dark:text-amber-400">
                        Baca Rincian Pengumuman &rarr;
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-3 rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-sm text-zinc-500 dark:border-zinc-800">
                Tidak ditemukan pengumuman yang sesuai dengan kriteria pencarian.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $announcements->links() }}
    </div>
</div>
