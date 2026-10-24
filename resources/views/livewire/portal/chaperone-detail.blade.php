<x-admin.doc-viewer-scope>
    <x-official-header />

    {{-- Navigasi & Aksi --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('scout-group.chaperones') }}" wire:navigate variant="subtle" icon="arrow-left" size="sm" aria-label="Kembali ke daftar bindamping" />
            <div>
                <div class="flex flex-wrap items-center gap-2">
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

        <div class="flex flex-wrap items-center gap-2">
            @if ($scoutGroup->isEditable())
                <flux:button href="{{ route('scout-group.chaperones.edit', $chaperone->id) }}" wire:navigate variant="primary" size="sm" icon="pencil-square">
                    Ubah Data
                </flux:button>
            @endif

            @if ($scoutGroup->canDownloadDocuments())
                <flux:button href="{{ route('scout-group.chaperones.print', $chaperone->id) }}" target="_blank" variant="subtle" size="sm" icon="printer">
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
