@props([
    'method' => 'handleScan',
    'readerId' => 'qr-reader',
])

{{-- Pemindai QR kamera (html5-qrcode); hasil pindai dikirim ke method Livewire. Pustaka dimuat lewat @assets pada halaman induk. --}}
<div wire:key="qr-scanner-{{ $readerId }}"
    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900"
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
                try {
                    await this.scanner.stop();
                } catch (_) {}
                this.running = false;
            }
        },
        async switchCamera() {
            await this.stop();
            await this.start();
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
    <div class="relative aspect-video w-full overflow-hidden rounded-xl bg-zinc-900 sm:aspect-square">
        <div wire:ignore id="{{ $readerId }}" class="size-full [&_video]:size-full [&_video]:object-cover [&_#qr-shaded-region]:hidden"></div>

        {{-- Overlay Target Reticle (Kotak Pemindai QR Rapi di Tengah) --}}
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center z-10" x-show="running">
            <div class="relative size-44 sm:size-52 md:size-56 border-2 rounded-3xl flex items-center justify-center transition-all duration-300"
                :class="locked ? 'border-emerald-400 shadow-[0_0_30px_rgba(16,185,129,0.35)]' : 'border-amber-400/50 shadow-[0_0_30px_rgba(245,158,11,0.25)]'">

                {{-- 4 Sudut Aksen Tebal --}}
                <div class="absolute -top-1 -left-1 size-7 sm:size-8 border-t-4 border-l-4 rounded-tl-2xl transition-colors duration-300"
                    :class="locked ? 'border-emerald-400' : 'border-amber-400'"></div>
                <div class="absolute -top-1 -right-1 size-7 sm:size-8 border-t-4 border-r-4 rounded-tr-2xl transition-colors duration-300"
                    :class="locked ? 'border-emerald-400' : 'border-amber-400'"></div>
                <div class="absolute -bottom-1 -left-1 size-7 sm:size-8 border-b-4 border-l-4 rounded-bl-2xl transition-colors duration-300"
                    :class="locked ? 'border-emerald-400' : 'border-amber-400'"></div>
                <div class="absolute -bottom-1 -right-1 size-7 sm:size-8 border-b-4 border-r-4 rounded-br-2xl transition-colors duration-300"
                    :class="locked ? 'border-emerald-400' : 'border-amber-400'"></div>

                {{-- Titik Fokus / Crosshair Minimalis --}}
                <div class="size-4 border rounded-full flex items-center justify-center transition-colors duration-300"
                    :class="locked ? 'border-emerald-400/50' : 'border-amber-400/40'">
                    <div class="size-1.5 rounded-full transition-colors duration-300"
                        :class="locked ? 'bg-emerald-400' : 'bg-amber-400/80'"></div>
                </div>

                {{-- Laser Garis Pemindai Animasi --}}
                <div x-show="!locked" class="absolute inset-x-2 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent shadow-[0_0_12px_#f59e0b] animate-scan"></div>
            </div>
        </div>

        {{-- Keterangan / Petunjuk Pemindaian (DILUAR BOX SCAN QR) --}}
        <div class="pointer-events-none absolute bottom-3 sm:bottom-4 inset-x-0 flex justify-center z-20 px-3" x-show="running">
            <div class="inline-flex items-center gap-2 rounded-full bg-black/85 px-3.5 py-1.5 sm:px-4 sm:py-2 border border-zinc-700/80 backdrop-blur-md shadow-2xl text-[10px] sm:text-xs">
                <span class="inline-flex size-2 rounded-full shrink-0"
                    :class="locked ? 'bg-emerald-400 animate-ping' : 'bg-amber-400 animate-pulse'"></span>
                <span class="font-bold tracking-wider uppercase transition-colors"
                    :class="locked ? 'text-emerald-300' : 'text-amber-300'"
                    x-text="locked ? 'MEMPROSES KODE QR...' : 'ARAHKAN QR CODE KE SINI'">
                </span>
            </div>
        </div>

        {{-- State Kamera Belum Aktif --}}
        <div x-show="!running" class="absolute inset-0 flex flex-col items-center justify-center gap-2 p-6 text-center text-zinc-400">
            <div class="size-14 rounded-2xl bg-zinc-800/80 border border-zinc-700/60 flex items-center justify-center text-amber-500 mb-1 shadow-inner">
                <flux:icon name="viewfinder-circle" class="size-7" />
            </div>
            <h4 class="text-sm font-bold text-zinc-200">Kamera Belum Aktif</h4>
            <p class="text-xs text-zinc-400 max-w-xs">Tekan tombol di bawah untuk mulai memindai QR kartu peserta/bindamping.</p>
        </div>
    </div>

    <p x-show="error" x-text="error" class="mt-3 rounded-lg bg-rose-50 p-2.5 text-xs text-rose-700 dark:bg-rose-950/40 dark:text-rose-300"></p>

    <div class="mt-3 flex flex-wrap items-center gap-2">
        <flux:button x-show="!running" x-on:click="start()" variant="primary" icon="camera" class="flex-1">Mulai Pindai</flux:button>
        <flux:button x-show="running" x-on:click="stop()" variant="danger" icon="stop" class="flex-1">Hentikan</flux:button>
        <flux:button x-show="running && cameras.length > 1" x-on:click="switchCamera()" icon="arrow-path">Ganti Kamera</flux:button>
    </div>

    <select x-show="cameras.length > 1" x-model="cameraId" x-on:change="running && switchCamera()"
        class="mt-3 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
        <template x-for="camera in cameras" :key="camera.id">
            <option :value="camera.id" x-text="camera.label || camera.id"></option>
        </template>
    </select>
</div>
