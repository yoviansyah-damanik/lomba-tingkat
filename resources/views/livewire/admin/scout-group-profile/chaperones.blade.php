{{-- Pembina Pendamping --}}
<section class="space-y-4">
    <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Pembina Pendamping</h3>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @forelse($g->chaperones as $chaperone)
            <div wire:key="chaperone-{{ $chaperone->id }}" class="flex items-start justify-between gap-3 rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="min-w-0">
                    <div class="text-sm font-bold text-zinc-900 dark:text-white">{{ $chaperone->full_name }}</div>
                    <div class="mt-0.5 text-xs text-zinc-500">{{ $chaperone->gender === 'male' ? 'Pembina Putra' : 'Pembina Putri' }} &bull; NTA {{ $chaperone->scout_id ?: '-' }}</div>
                    <div class="mt-1 font-mono text-xs text-zinc-500">{{ $chaperone->id_number ?: '-' }} &bull; {{ $chaperone->phone_number ?: '-' }}</div>
                </div>
                <flux:button href="{{ route('admin.gudep-directory.chaperones.show', [$g->id, $chaperone->id]) }}" wire:navigate variant="subtle" size="xs" icon="eye" class="shrink-0">
                    Lihat Detail
                </flux:button>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-300 p-8 text-center text-xs italic text-zinc-400 dark:border-zinc-700 sm:col-span-2">Belum ada bindamping.</div>
        @endforelse
    </div>
</section>