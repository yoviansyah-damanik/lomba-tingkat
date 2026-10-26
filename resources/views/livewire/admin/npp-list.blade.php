<div class="space-y-3">
    <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
            NPP Regu {{ $gender === 'boys' ? 'Putra' : 'Putri' }}
            <span class="ml-1 text-xs font-medium text-zinc-500">({{ $patrols->count() }})</span>
        </h3>
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari NPP, regu, atau pangkalan" class="ml-auto max-w-xs" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="max-h-[560px] overflow-auto">
            <table class="w-full text-left text-sm">
                <thead class="sticky top-0 border-b border-zinc-200 bg-zinc-50 text-xs font-semibold text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="py-3 pl-5 pr-3">NPP</th>
                        <th class="px-3 py-3">Regu</th>
                        <th class="px-3 py-3">Gudep / Pangkalan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($patrols as $patrol)
                        <tr wire:key="npp-{{ $patrol->id }}">
                            <td class="py-2.5 pl-5 pr-3">
                                <span class="rounded-lg bg-zinc-100 px-2 py-0.5 font-mono text-xs font-extrabold text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">{{ $patrol->npp }}</span>
                            </td>
                            <td class="px-3 py-2.5 font-semibold text-zinc-900 dark:text-zinc-100">{{ $patrol->patrol_name }}</td>
                            <td class="px-3 py-2.5 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $patrol->scoutGroup?->school_name ?? '-' }}
                                <span class="block text-[11px] text-zinc-400">Gudep {{ $patrol->group_number }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-xs text-zinc-500">Belum ada NPP yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
