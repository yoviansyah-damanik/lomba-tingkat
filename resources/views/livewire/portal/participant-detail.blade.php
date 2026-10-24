<x-admin.doc-viewer-scope>
    <x-official-header />

    {{-- Navigasi & Aksi --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('scout-group.patrols') }}" wire:navigate variant="subtle" icon="arrow-left" size="sm" aria-label="Kembali ke daftar regu" />
            <div>
                <div class="flex flex-wrap items-center gap-2">
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

        <div class="flex flex-wrap items-center gap-2">
            @if ($scoutGroup->isEditable())
                <flux:button href="{{ route('scout-group.participants.edit', $participant->id) }}" wire:navigate variant="primary" size="sm" icon="pencil-square">
                    Ubah Data
                </flux:button>
            @endif

            @if ($scoutGroup->canDownloadDocuments())
                <flux:button href="{{ route('scout-group.participants.print', $participant->id) }}" target="_blank" variant="subtle" size="sm" icon="printer">
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
