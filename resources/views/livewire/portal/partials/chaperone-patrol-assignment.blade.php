@php
    $assignable = $scoutGroup->patrols->filter(fn ($p) => $gender === 'male' ? $p->isBoys() : $p->isGirls())->values();
@endphp

{{-- Regu yang didampingi: bindamping ditetapkan ke regu sekategori saat bindamping didaftarkan --}}
<div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
    <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Penugasan</span>
        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Regu yang Didampingi</h2>
        <p class="text-xs text-zinc-400">Pilih regu {{ $gender === 'male' ? 'putra' : 'putri' }} yang didampingi bindamping ini. Satu bindamping dapat mendampingi beberapa regu.</p>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
        @forelse($assignable as $patrol)
            @php $takenBy = $patrol->chaperone_id && $patrol->chaperone_id !== ($chaperone?->id) ? $patrol->chaperone : null; @endphp
            <label wire:key="assign-{{ $patrol->id }}" class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 p-3 text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/60">
                <input type="checkbox" wire:model="patrol_ids" value="{{ $patrol->id }}" class="mt-0.5 size-4 rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                <span class="min-w-0">
                    <span class="block font-semibold text-zinc-900 dark:text-zinc-100">Regu {{ $patrol->patrol_name }}</span>
                    <span class="block text-xs text-zinc-500">
                        {{ $takenBy ? 'Saat ini didampingi '.$takenBy->full_name.' (akan dialihkan)' : 'Belum ada bindamping lain' }}
                    </span>
                </span>
            </label>
        @empty
            <p class="text-xs italic text-zinc-400">Belum ada regu {{ $gender === 'male' ? 'putra' : 'putri' }}.</p>
        @endforelse
    </div>
    @error('patrol_ids') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
    @error('patrol_ids.*') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
</div>
