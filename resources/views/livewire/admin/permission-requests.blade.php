@assets
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endassets

<div class="flex h-full w-full flex-1 flex-col gap-6" x-on:permission-letter-ready.window="window.open($event.detail.url, '_blank')">
    <x-heading-section
        title="Permohonan Izin"
        description="Catat izin meninggalkan perkemahan (permanen atau sementara) dan kunjungan tamu. Izin meninggalkan perkemahan wajib memindai QR peserta dan bindamping."
        badge="Hanya Administrator"
        badge-icon="clipboard-document-check"
        badge-color="amber"
    />

    {{-- Tab --}}
    <div class="flex flex-wrap items-center gap-2" role="tablist">
        <flux:button wire:click="setTab('create')" size="sm" :variant="$tab === 'create' ? 'primary' : 'subtle'" icon="plus">Permohonan Baru</flux:button>
        <flux:button wire:click="setTab('return')" size="sm" :variant="$tab === 'return' ? 'primary' : 'subtle'" icon="arrow-uturn-left">Konfirmasi Kembali</flux:button>
        <flux:button wire:click="setTab('history')" size="sm" :variant="$tab === 'history' ? 'primary' : 'subtle'" icon="clock">Riwayat</flux:button>
    </div>

    @if ($feedback)
        <div class="flex items-start gap-2 rounded-xl border p-3 text-sm {{ $feedback['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-500/30 dark:bg-red-950/30 dark:text-red-200' }}" role="status">
            <flux:icon :name="$feedback['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'" class="mt-0.5 size-4 shrink-0" />
            <span>{{ $feedback['message'] }}</span>
            @if ($lastLetterUrl && $feedback['type'] === 'success')
                <flux:button href="{{ $lastLetterUrl }}" target="_blank" size="xs" variant="primary" icon="printer" class="ml-auto shrink-0">Cetak Surat Izin</flux:button>
            @endif
        </div>
    @endif

    @if ($tab === 'create')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            {{-- Form permohonan --}}
            <form wire:submit="submit" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-7">
                <flux:select wire:model.live="type" label="Jenis Izin">
                    @foreach ($types as $option)
                        <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                @if (! $selectedType->requiresScan())
                    <flux:input wire:model="guest_name" label="Nama Tamu" placeholder="Nama tamu yang berkunjung (opsional)" />
                @endif

                <flux:textarea wire:model="reason" label="Keterangan" rows="3" placeholder="Isi alasan permohonan izin..." />
                @error('reason') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                @if ($selectedType->requiresScan())
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Peserta & Bindamping ({{ count($members) }})</h3>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Pindai QR minimal 1 bindamping dan 1 peserta dari gudep yang sama. Peserta boleh lebih dari satu.</p>

                        <ul class="mt-3 divide-y divide-zinc-100 rounded-xl border border-zinc-200 text-sm dark:divide-zinc-800 dark:border-zinc-800">
                            @forelse ($members as $index => $member)
                                <li wire:key="member-{{ $member['id'] }}" class="flex items-center justify-between gap-3 px-3 py-2.5">
                                    <div class="min-w-0">
                                        <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $member['name'] }}</span>
                                        <span class="text-[11px] text-zinc-500">{{ $member['kind'] === 'participant' ? 'Peserta' : 'Bindamping' }} &bull; NTA {{ $member['scout_id'] }} &bull; {{ $member['role'] }} &bull; {{ $member['group_name'] }}</span>
                                    </div>
                                    <flux:button wire:click="removeMember({{ $index }})" size="xs" variant="subtle" icon="x-mark" />
                                </li>
                            @empty
                                <li class="px-3 py-4 text-center text-xs italic text-zinc-400">Belum ada QR yang dipindai.</li>
                            @endforelse
                        </ul>
                        @error('members') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                @endif

                <flux:button type="submit" variant="primary" icon="paper-airplane" class="w-full">Simpan Permohonan Izin</flux:button>
            </form>

            {{-- Pemindai --}}
            @if ($selectedType->requiresScan())
                <div class="space-y-4 lg:col-span-5">
                    <x-qr-scanner method="handleScan" reader-id="permission-qr-reader" />
                    @include('livewire.admin.partials.permission-manual-scan')
                </div>
            @endif
        </div>
    @elseif ($tab === 'return')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            <div class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-7">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Konfirmasi Kembali (Izin Sementara)</h3>
                <p class="text-xs text-zinc-500">Pindai QR peserta yang kembali ke perkemahan. Hanya peserta yang perlu dikonfirmasi; bindamping tidak. Izin dinyatakan selesai setelah seluruh peserta pada izin tersebut kembali.</p>
            </div>

            <div class="space-y-4 lg:col-span-5">
                <x-qr-scanner method="handleScan" reader-id="permission-return-qr-reader" />
                @include('livewire.admin.partials.permission-manual-scan')
            </div>
        </div>
    @else
        <livewire:admin.permission-requests-list lazy />
    @endif
</div>
