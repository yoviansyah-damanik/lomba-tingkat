<x-admin.doc-viewer-scope>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('admin.gudep-directory.show', $scoutGroup->id) }}" wire:navigate variant="subtle" icon="arrow-left" size="sm" />
            <div>
                <div class="flex items-center gap-2">
                    <x-id-badge :value="$chaperone->id_number" />
                    <span class="rounded px-2 py-0.5 text-xs font-bold {{ $chaperone->gender === 'male' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                        {{ $chaperone->gender === 'male' ? 'Pembina Putra' : 'Pembina Putri' }}
                    </span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">{{ $chaperone->full_name }}</h1>
            </div>
        </div>

        <flux:button href="{{ route('admin.gudep-directory.show', $scoutGroup->id) }}" wire:navigate variant="subtle" size="sm" icon="building-library">
            {{ $scoutGroup->school_name }}
        </flux:button>
    </div>

    @include('admin.chaperones.partials.detail-body')

    @include('admin.chaperones.partials.doc-modal')
</x-admin.doc-viewer-scope>
