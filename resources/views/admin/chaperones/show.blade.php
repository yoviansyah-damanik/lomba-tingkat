<x-layouts::app title="Detail Pembina Pendamping — {{ $chaperone->full_name }}">
    <x-admin.doc-viewer-scope>
        <x-official-header />

        {{-- Top Navigation & Action Bar --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <flux:button href="{{ $scoutGroup ? route('admin.scout-groups.show', $scoutGroup->id) : route('admin.chaperones.index') }}" variant="subtle" icon="arrow-left" size="sm">
                </flux:button>
                <div>
                    <div class="flex items-center gap-2">
                        <x-id-badge :value="$chaperone->id_number" />
                        <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $chaperone->status?->badgeClasses() }}">
                            {{ $chaperone->status?->label() ?? 'Status Belum Diverifikasi' }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $chaperone->gender === 'male' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                            {{ $chaperone->gender === 'male' ? 'Bindamping Putra' : 'Bindamping Putri' }}
                        </span>
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                        {{ $chaperone->full_name }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($scoutGroup)
                    <flux:button href="{{ route('admin.scout-groups.show', $scoutGroup->id) }}" variant="subtle" size="sm" icon="building-library">
                        Gudep: {{ $scoutGroup->school_name }}
                    </flux:button>
                @endif

                @if($scoutGroup && $scoutGroup->canDownloadDocuments())
                    <flux:button href="{{ route('admin.scout-groups.chaperones.print', [$scoutGroup->id, $chaperone->id]) }}" target="_blank" variant="subtle" size="sm" icon="printer">
                        Cetak Form Biodata
                    </flux:button>
                    <flux:button href="{{ route('digital-cards.chaperone', $chaperone->id) }}" target="_blank" variant="subtle" size="sm" icon="qr-code">
                        Kartu Digital
                    </flux:button>
                @endif
            </div>
        </div>
        @include('admin.chaperones.partials.detail-body')

        @include('admin.chaperones.partials.doc-modal')
    </x-admin.doc-viewer-scope>
</x-layouts::app>
