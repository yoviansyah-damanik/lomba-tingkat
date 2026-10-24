<x-admin.doc-viewer-scope>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('admin.gudep-directory.show', $scoutGroup->id) }}" wire:navigate variant="subtle" icon="arrow-left" size="sm" />
            <div>
                <div class="flex items-center gap-2">
                    <x-id-badge :value="$participant->id_number" />
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $participant->role_in_patrol?->badgeClasses() }}">
                        {{ $participant->role_in_patrol?->label() }}
                    </span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">{{ $participant->full_name }}</h1>
            </div>
        </div>

        <flux:button href="{{ route('admin.gudep-directory.show', $scoutGroup->id) }}" wire:navigate variant="subtle" size="sm" icon="building-library">
            {{ $scoutGroup->school_name }}
        </flux:button>
    </div>

    @include('admin.participants.partials.detail-body')

    @include('admin.participants.partials.doc-modal')
</x-admin.doc-viewer-scope>
