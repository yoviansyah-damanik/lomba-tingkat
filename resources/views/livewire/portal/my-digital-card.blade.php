<div class="space-y-6">
    <x-heading-section
        title="Kartu Digital"
        description="Kartu tanda pengenal digital Anda pada kegiatan LT III. Tunjukkan QR Code kepada petugas untuk verifikasi dan presensi."
        badge="ID Card"
        badge-icon="qr-code"
        badge-color="amber"
    >
        @if(! empty($card['qrDataUri']))
            <x-slot:actions>
                <flux:button variant="primary" icon="arrow-down-tray" onclick="window.saveCardAsPng('{{ $filename }}', document.querySelector('#official-id-card [data-digital-card]'))">
                    Unduh ID Card
                </flux:button>
                <flux:button href="{{ route('digital-cards.verify', $card['token']) }}" target="_blank" variant="subtle" icon="shield-check">
                    Halaman Verifikasi
                </flux:button>
            </x-slot:actions>
        @endif
    </x-heading-section>

    @if(! $type)
        <div class="rounded-2xl border border-dashed border-zinc-300 p-10 text-center text-sm text-zinc-500 dark:border-zinc-700">
            Akun Anda belum terhubung dengan data kartu digital. Hubungi panitia bila ini keliru.
        </div>
    @elseif($lockedMessage)
        <div class="flex items-start gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/30 dark:text-amber-200">
            <flux:icon name="lock-closed" class="size-5 shrink-0" />
            <p>{{ $lockedMessage }}</p>
        </div>
    @else
        {{-- Kartu layar: identitas dan QR diperbesar untuk ditunjukkan ke petugas --}}
        @include('livewire.portal.partials.display-card', ['display' => $display, 'qrDataUri' => $card['qrDataUri'], 'token' => $card['token']])

        {{-- ID Card resmi (ukuran B2) untuk diunduh sebagai PNG --}}
        <section class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">ID Card Resmi (79 x 126 mm)</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Format cetak resmi kegiatan. Unduh sebagai gambar PNG untuk dicetak atau disimpan.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <flux:button icon="arrows-pointing-out" x-on:click="$dispatch('open-card-preview', {{ \Illuminate\Support\Js::from(['target' => '#official-id-card [data-digital-card]', 'filename' => $filename, 'title' => 'ID Card — '.($display['name'] ?? '')]) }})">
                        Layar Penuh
                    </flux:button>
                    <flux:button variant="primary" icon="arrow-down-tray" onclick="window.saveCardAsPng('{{ $filename }}', document.querySelector('#official-id-card [data-digital-card]'))">
                        Unduh ID Card
                    </flux:button>
                </div>
            </div>

            <div id="official-id-card" class="flex cursor-zoom-in justify-center overflow-x-auto py-2" x-on:click="$dispatch('open-card-preview', {{ \Illuminate\Support\Js::from(['target' => '#official-id-card [data-digital-card]', 'filename' => $filename, 'title' => 'ID Card — '.($display['name'] ?? '')]) }})">
                @if($type === 'participant')
                    @include('digital-cards.partials.participant-card', $card)
                @elseif($type === 'chaperone')
                    @include('digital-cards.partials.chaperone-card', $card)
                @else
                    @include('digital-cards.partials.staff-card', $card)
                @endif
            </div>
        </section>
    @endif
</div>
