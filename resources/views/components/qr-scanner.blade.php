@props([
    'method' => 'handleScan',
    'readerId' => 'qr-reader',
])

{{-- Pemindai QR kamera (html5-qrcode); hasil pindai dikirim ke method Livewire. Pustaka dimuat lewat @assets pada halaman induk. --}}
<div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900"
    x-data="{
        cameras: [],
        cameraId: '',
        running: false,
        locked: false,
        error: '',
        scanner: null,
        async start() {
            this.error = '';
            if (typeof Html5Qrcode === 'undefined') {
                this.error = 'Pustaka pemindai belum termuat. Muat ulang halaman.';
                return;
            }
            try {
                if (!this.cameras.length) {
                    this.cameras = await Html5Qrcode.getCameras();
                    const back = this.cameras.find(c => /back|belakang|environment/i.test(c.label));
                    this.cameraId = (back ?? this.cameras[0])?.id ?? '';
                }
                if (!this.cameraId) {
                    this.error = 'Kamera tidak ditemukan. Pastikan perangkat memiliki kamera dan izin akses diberikan.';
                    return;
                }
                this.scanner = new Html5Qrcode('{{ $readerId }}');
                await this.scanner.start(this.cameraId, { fps: 15 }, (text) => this.onScan(text), () => {});
                this.running = true;
            } catch (e) {
                this.error = 'Kamera tidak dapat diakses: ' + (e.message || e);
            }
        },
        async stop() {
            if (this.scanner && this.running) {
                await this.scanner.stop();
                this.running = false;
            }
        },
        onScan(text) {
            if (this.locked) return;
            this.locked = true;
            $wire.{{ $method }}(text);
            if (navigator.vibrate) navigator.vibrate(80);
            setTimeout(() => this.locked = false, 2000);
        },
    }"
    x-on:livewire:navigate.window="stop()">
    <div wire:ignore class="relative aspect-video w-full overflow-hidden rounded-xl bg-zinc-900 sm:aspect-square">
        <div id="{{ $readerId }}" class="size-full [&_video]:size-full [&_video]:object-cover"></div>
        <div x-show="!running" class="absolute inset-0 flex flex-col items-center justify-center gap-2 p-6 text-center text-zinc-400">
            <flux:icon name="viewfinder-circle" class="size-10" />
            <p class="text-xs">Kamera belum aktif. Tekan tombol di bawah untuk mulai memindai.</p>
        </div>
    </div>

    <p x-show="error" x-text="error" class="mt-3 rounded-lg bg-rose-50 p-2.5 text-xs text-rose-700 dark:bg-rose-950/40 dark:text-rose-300"></p>

    <div class="mt-3 flex flex-wrap items-center gap-2">
        <flux:button x-show="!running" x-on:click="start()" variant="primary" icon="camera" class="flex-1">Mulai Pindai</flux:button>
        <flux:button x-show="running" x-on:click="stop()" variant="danger" icon="stop" class="flex-1">Hentikan</flux:button>
    </div>
</div>
