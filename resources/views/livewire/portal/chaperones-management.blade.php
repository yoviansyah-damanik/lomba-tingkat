<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-official-header />

    <x-heading-section
        title="Pembina Pendamping (Bindamping)"
        description="Daftarkan pembina pendamping resmi yang mendampingi regu selama kegiatan LT III Kwarcab Padangsidimpuan. Satu bindamping dapat mendampingi beberapa regu sekategori."
        badge="Administrasi Kontingen"
        badge-icon="user-group"
        badge-color="amber"
    >
        <x-slot:actions>
            @if($scoutGroup->chaperones->count() > 0)
                @if($scoutGroup->canDownloadDocuments())
                <flux:button href="{{ route('digital-cards.chaperone.batch', $scoutGroup->id) }}" target="_blank" variant="subtle" icon="qr-code">
                    ID Card Bindamping (.ZIP / .PNG)
                </flux:button>
                @endif
            @endif
        </x-slot:actions>
    </x-heading-section>

    @php
        $categories = [
            'male' => ['label' => 'Putra', 'patrolLabel' => 'Regu Putra', 'dot' => 'bg-blue-600', 'badge' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300', 'empty' => 'border-blue-300 bg-blue-50/40 dark:border-blue-900/40 dark:bg-blue-950/20', 'icon' => 'text-blue-400', 'card' => 'border-blue-200 dark:border-blue-900/50'],
            'female' => ['label' => 'Putri', 'patrolLabel' => 'Regu Putri', 'dot' => 'bg-rose-600', 'badge' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300', 'empty' => 'border-rose-300 bg-rose-50/40 dark:border-rose-900/40 dark:bg-rose-950/20', 'icon' => 'text-rose-400', 'card' => 'border-rose-200 dark:border-rose-900/50'],
        ];
    @endphp

    {{-- Bindamping per kategori: tanpa batas jumlah, ditetapkan per regu pada halaman Regu --}}
    @foreach($categories as $gender => $cat)
        @php $list = $chaperonesByGender[$gender]; @endphp
        <section class="space-y-4" wire:key="chaperone-category-{{ $gender }}">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex size-3 rounded-full {{ $cat['dot'] }}"></span>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Pembina Pendamping {{ $cat['label'] }}</h3>
                    <span class="rounded-md border px-2 py-0.5 font-mono text-[11px] font-bold {{ $cat['badge'] }}">
                        Gudep {{ $scoutGroup->{$gender.'_group_number'} ?? $scoutGroup->group_number }}
                    </span>
                    <span class="text-xs text-zinc-400">{{ $list->count() }} orang</span>
                </div>

                @if($scoutGroup->isEditable() && $this->canRegister($gender))
                    <flux:button href="{{ route('scout-group.chaperones.create', ['gender' => $gender]) }}" wire:navigate size="sm" variant="primary" icon="plus">
                        Tambah Bindamping {{ $cat['label'] }}
                    </flux:button>
                @endif
            </div>

            @if($list->isEmpty())
                <div class="rounded-2xl border border-dashed p-8 text-center {{ $cat['empty'] }}">
                    <flux:icon name="user-plus" class="mx-auto mb-2 size-10 opacity-80 {{ $cat['icon'] }}" />
                    <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Belum Ada Bindamping {{ $cat['label'] }}</p>
                    @if($scoutGroup->isEditable() && ! $this->canRegister($gender))
                        <p class="mx-auto mt-1 max-w-md text-[11px] text-zinc-500 dark:text-zinc-400">Tambahkan <strong>{{ $cat['patrolLabel'] }}</strong> terlebih dahulu. Bindamping {{ $cat['label'] }} didaftarkan setelah {{ $cat['patrolLabel'] }} ada.</p>
                        <flux:button href="{{ route('scout-group.patrols') }}" wire:navigate size="xs" variant="subtle" icon="user-group" class="mt-3">Kelola Regu</flux:button>
                    @else
                        <p class="mt-0.5 text-[11px] text-zinc-400">Minimal 1 bindamping {{ strtolower($cat['label']) }} bila ada {{ strtolower($cat['patrolLabel']) }}</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @foreach($list as $chaperone)
                        <div wire:key="chaperone-{{ $chaperone->id }}" class="flex flex-col justify-between rounded-2xl border bg-white p-6 shadow-sm dark:bg-zinc-900 {{ $cat['card'] }}">
                            <div class="flex items-start gap-4">
                                <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                    @if($chaperone->photo_path)
                                        <img src="{{ asset('storage/' . $chaperone->photo_path) }}" class="size-full object-cover" alt="Foto">
                                    @else
                                        <flux:icon name="user" class="size-8 text-zinc-400" />
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $chaperone->full_name }}</h4>
                                    <div class="mt-1 space-y-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        <p>No. WhatsApp: <strong class="text-zinc-800 dark:text-zinc-200">{{ $chaperone->phone_number }}</strong></p>
                                        <p>NTA Pramuka: <span class="font-mono">{{ $chaperone->scout_id ?: '-' }}</span></p>
                                        <p>ID: <span class="font-mono font-bold text-amber-700 dark:text-amber-400">{{ $chaperone->id_number ?: '-' }}</span></p>
                                    </div>
                                    <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[11px] font-semibold {{ $chaperone->status?->badgeClasses() ?? 'bg-zinc-100 text-zinc-700' }}">
                                            {{ $chaperone->status?->label() ?? 'Draft' }}
                                        </span>
                                        @forelse($chaperone->patrols as $patrol)
                                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Regu {{ $patrol->patrol_name }}</span>
                                        @empty
                                            <span class="text-[11px] italic text-amber-600 dark:text-amber-400">Belum mendampingi regu</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:button href="{{ route('scout-group.chaperones.show', $chaperone->id) }}" wire:navigate variant="subtle" size="xs" icon="eye" title="Lihat detail lengkap bindamping">
                                        Detail
                                    </flux:button>
                                    @if($scoutGroup->canDownloadDocuments())
                                        <flux:button href="{{ route('scout-group.chaperones.print', $chaperone->id) }}" target="_blank" variant="subtle" size="xs" icon="printer"
                                            data-loading="Mencetak Biodata Bindamping ({{ $cat['label'] }}) (PDF)...">
                                            Biodata
                                        </flux:button>
                                        <flux:button href="{{ route('digital-cards.chaperone', $chaperone->id) }}" target="_blank" variant="subtle" size="xs" icon="qr-code"
                                            data-loading="Memuat Kartu Digital Bindamping ({{ $cat['label'] }})...">
                                            Kartu
                                        </flux:button>
                                    @endif
                                    @if($scoutGroup->isEditable())
                                        <flux:button href="{{ route('scout-group.chaperones.edit', $chaperone->id) }}" wire:navigate variant="subtle" size="xs" icon="pencil-square">
                                            Edit
                                        </flux:button>
                                    @endif
                                </div>
                                @if($scoutGroup->isEditable())
                                    <flux:button
                                        type="button"
                                        wire:click="deleteChaperone('{{ $chaperone->id }}')"
                                        wire:confirm="Hapus Bindamping {{ $cat['label'] }} ini? Regu yang didampingi akan kembali tanpa bindamping."
                                        variant="danger"
                                        size="xs"
                                        icon="trash"
                                    >
                                        Hapus
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach
</div>
