@assets
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endassets

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-heading-section
        title="Verifikasi Identitas"
        description="Pindai QR Code pada kartu digital peserta, pembina pendamping, panitia, atau juri untuk memastikan identitas dan status verifikasinya."
        badge="Verifikasi Lapangan"
        badge-icon="viewfinder-circle"
        badge-color="amber"
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start"
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
                    this.scanner = new Html5Qrcode('identity-qr-reader');
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
            async switchCamera() {
                await this.stop();
                await this.start();
            },
            onScan(text) {
                if (this.locked) return;
                this.locked = true;
                $wire.verifyToken(text);
                if (navigator.vibrate) navigator.vibrate(80);
                setTimeout(() => this.locked = false, 2500);
            },
        }"
        x-on:livewire:navigate.window="stop()">

        {{-- Pemindai --}}
        <div class="space-y-4 lg:col-span-5">
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div wire:ignore class="relative aspect-square w-full overflow-hidden rounded-xl bg-zinc-900">
                    <div id="identity-qr-reader" class="size-full [&_video]:size-full [&_video]:object-cover"></div>
                    <div x-show="!running" class="absolute inset-0 flex flex-col items-center justify-center gap-2 p-6 text-center text-zinc-400">
                        <flux:icon name="viewfinder-circle" class="size-12" />
                        <p class="text-xs">Kamera belum aktif. Tekan tombol di bawah untuk mulai memindai.</p>
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

            {{-- Input manual (URL verifikasi atau token) --}}
            <form wire:submit="verifyManual" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <flux:input wire:model="manualInput" label="Input Manual" placeholder="Tempel tautan verifikasi atau token QR" icon="link" />
                <flux:button type="submit" variant="subtle" icon="magnifying-glass" class="mt-3 w-full">Verifikasi</flux:button>
            </form>

            {{-- Riwayat pemindaian --}}
            @if ($history)
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500">Pemindaian Terakhir</h3>
                    <ul class="divide-y divide-zinc-100 text-xs dark:divide-zinc-800">
                        @foreach ($history as $index => $entry)
                            <li wire:key="history-{{ $index }}-{{ $entry['scanned_at'] }}" class="flex items-center justify-between gap-2 py-2">
                                <div class="min-w-0">
                                    <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $entry['name'] }}</span>
                                    <span class="text-[11px] text-zinc-500">{{ $entry['role_label'] }} &bull; {{ $entry['scanned_at'] }} WIB</span>
                                </div>
                                @if (! $entry['valid'])
                                    <flux:badge size="sm" color="red">Tidak Valid</flux:badge>
                                @elseif ($entry['is_verified'])
                                    <flux:badge size="sm" color="emerald">Terverifikasi</flux:badge>
                                @else
                                    <flux:badge size="sm" color="amber">Belum Terverifikasi</flux:badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Hasil verifikasi --}}
        <div class="lg:col-span-7">
            @if (! $result)
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="qr-code" class="mx-auto mb-2 size-10 text-zinc-400" />
                    <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Menunggu pemindaian</p>
                    <p class="mt-1 text-xs text-zinc-500">Arahkan kamera ke QR Code pada kartu digital. Hasil verifikasi tampil di sini.</p>
                </div>
            @elseif (! $result['valid'])
                <div class="rounded-2xl border border-rose-300 bg-rose-50 p-6 text-center dark:border-rose-800 dark:bg-rose-950/40">
                    <flux:icon name="x-circle" class="mx-auto mb-2 size-12 text-rose-600" />
                    <h2 class="text-base font-extrabold uppercase text-rose-800 dark:text-rose-200">QR Code Tidak Valid</h2>
                    <p class="mt-1 text-xs text-rose-700 dark:text-rose-300">Kartu tidak ditemukan dalam sistem. Kemungkinan kartu palsu, kedaluwarsa, atau QR bukan dari aplikasi ini.</p>
                    <p class="mt-3 break-all font-mono text-[11px] text-rose-600">{{ $result['raw'] }}</p>
                    <flux:button wire:click="clearResult" variant="subtle" size="sm" icon="x-mark" class="mt-4">Tutup</flux:button>
                </div>
            @else
                @php
                    $banner = $result['is_verified']
                        ? ['border' => 'border-emerald-300 dark:border-emerald-800', 'head' => 'bg-emerald-600', 'icon' => 'check-circle', 'text' => 'Identitas Terverifikasi']
                        : ['border' => 'border-amber-300 dark:border-amber-800', 'head' => 'bg-amber-600', 'icon' => 'exclamation-triangle', 'text' => 'Belum Terverifikasi'];
                @endphp

                <div class="overflow-hidden rounded-2xl border {{ $banner['border'] }} bg-white shadow-sm dark:bg-zinc-900">
                    <div class="{{ $banner['head'] }} flex items-center gap-3 px-5 py-3 text-white">
                        <flux:icon :name="$banner['icon']" class="size-7" />
                        <div>
                            <div class="text-sm font-extrabold uppercase tracking-wide">{{ $banner['text'] }}</div>
                            <div class="text-[11px] text-white/90">{{ $result['title'] }} &bull; {{ $result['status_label'] }}</div>
                        </div>
                        <span class="ml-auto text-[11px] text-white/80">{{ $result['scanned_at'] }} WIB</span>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center gap-4 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                            <div class="flex h-24 w-[72px] shrink-0 items-center justify-center overflow-hidden rounded-xl border-2 border-amber-500 bg-zinc-100 dark:bg-zinc-800">
                                @if ($result['photo_url'])
                                    <img src="{{ $result['photo_url'] }}" alt="{{ $result['name'] }}" class="size-full object-cover">
                                @else
                                    <span class="text-[10px] font-bold text-zinc-400">FOTO</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <flux:badge size="sm" color="amber">{{ $result['category_label'] }}</flux:badge>
                                <h2 class="mt-1 text-lg font-extrabold leading-tight text-zinc-900 dark:text-zinc-100">{{ $result['name'] }}</h2>
                                <p class="text-xs font-medium text-zinc-500">{{ $result['role_label'] }}</p>
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-3 text-xs sm:grid-cols-2">
                            <div>
                                <dt class="text-zinc-400">Nomor ID Resmi</dt>
                                <dd class="font-mono font-bold text-amber-700 dark:text-amber-400">{{ $result['id_number'] ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-zinc-400">{{ $result['type'] === 'staff' ? 'Instansi' : 'Pangkalan / Sekolah' }}</dt>
                                <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['school_name'] }}</dd>
                            </div>
                            @if ($result['type'] !== 'staff')
                                <div>
                                    <dt class="text-zinc-400">{{ $result['type'] === 'participant' ? 'Regu' : 'Penugasan' }}</dt>
                                    <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['patrol_name'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Nomor Gudep</dt>
                                    <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['group_number'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">NTA</dt>
                                    <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['scout_id'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Jenis Kelamin</dt>
                                    <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['gender'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Tempat, Tanggal Lahir</dt>
                                    <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['birth_info'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Golongan Darah</dt>
                                    <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['blood_type'] }}</dd>
                                </div>
                            @else
                                <div>
                                    <dt class="text-zinc-400">Jabatan</dt>
                                    <dd class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['patrol_name'] }}</dd>
                                </div>
                            @endif
                        </dl>

                        @unless ($result['is_verified'])
                            <p class="mt-4 rounded-xl bg-amber-50 p-3 text-[11px] leading-relaxed text-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                                {{ $result['type'] === 'staff'
                                    ? 'Akun aparatur ini nonaktif. Jangan izinkan akses sebelum diperiksa.'
                                    : 'Kontingen pemilik kartu ini belum diverifikasi Administrator. Periksa status pendaftaran gugus depan.' }}
                            </p>
                        @endunless

                        <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            @if ($result['detail_url'])
                                <flux:button href="{{ $result['detail_url'] }}" wire:navigate variant="subtle" size="sm" icon="identification">Lihat Detail Lengkap</flux:button>
                            @endif
                            <flux:button href="{{ $result['card_url'] }}" target="_blank" variant="subtle" size="sm" icon="qr-code">Kartu Digital</flux:button>
                            <flux:button wire:click="clearResult" variant="ghost" size="sm" icon="x-mark" class="ml-auto">Tutup Hasil</flux:button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
