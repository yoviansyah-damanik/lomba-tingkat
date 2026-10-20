<x-layouts::public title="Pengumuman Resmi">
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
        </x-heading-section>

        {{-- Filter & Search Form --}}
        <form method="GET" action="{{ route('announcements') }}" class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari pengumuman..."
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
            </div>

            <div class="flex items-center gap-2">
                <select
                    name="category"
                    class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="">Semua Kategori</option>
                    <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>Umum</option>
                    <option value="technical" {{ request('category') === 'technical' ? 'selected' : '' }}>Teknis</option>
                    <option value="schedule" {{ request('category') === 'schedule' ? 'selected' : '' }}>Jadwal</option>
                    <option value="urgent" {{ request('category') === 'urgent' ? 'selected' : '' }}>Penting / Darurat</option>
                </select>

                <button
                    type="submit"
                    class="rounded-xl bg-amber-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-amber-700"
                >
                    Saring
                </button>

                @if(request()->hasAny(['search', 'category']))
                    <a href="{{ route('announcements') }}" class="rounded-xl border border-zinc-300 px-3 py-2 text-xs sm:text-sm text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-400">
                        Atur Ulang
                    </a>
                @endif
            </div>
        </form>

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
                        <p class="mt-2 text-xs text-zinc-600 line-clamp-3 dark:text-zinc-400">
                            {{ $announcement->summary ?? Str::limit(strip_tags($announcement->content), 120) }}
                        </p>
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
</x-layouts::public>
