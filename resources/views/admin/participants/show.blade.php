<x-layouts::app title="Detail Peserta — {{ $participant->full_name }}">
    <x-admin.doc-viewer-scope>
        <x-official-header />

        {{-- Top Navigation & Action Bar --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <flux:button href="{{ $scoutGroup ? route('admin.scout-groups.show', $scoutGroup->id) : route('admin.participants.index') }}" variant="subtle" icon="arrow-left" size="sm">
                </flux:button>
                <div>
                    <div class="flex items-center gap-2">
                        <x-id-badge :value="$participant->id_number" />
                        <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $participant->status?->badgeClasses() }}">
                            {{ $participant->status?->label() ?? 'Status Belum Diverifikasi' }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $participant->role_in_patrol?->badgeClasses() }}">
                            {{ $participant->role_in_patrol?->label() }}
                        </span>
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                        {{ $participant->full_name }}
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
                    <flux:button href="{{ route('admin.scout-groups.participants.print', [$scoutGroup->id, $participant->id]) }}" target="_blank" variant="subtle" size="sm" icon="printer">
                        Cetak Form Biodata
                    </flux:button>
                    <flux:button href="{{ route('digital-cards.participant', $participant->id) }}" target="_blank" variant="subtle" size="sm" icon="qr-code">
                        Kartu Digital
                    </flux:button>
                @endif
            </div>
        </div>
        @include('admin.participants.partials.detail-body')

        @include('admin.participants.partials.doc-modal')
    </x-admin.doc-viewer-scope>
</x-layouts::app>
