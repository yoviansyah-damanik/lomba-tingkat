<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Siaran Notifikasi Web Push (PWA)"
        description="Kirimkan pemberitahuan instan langsung ke layar gawai panitia, juri, dan kontingen di lapangan perkemahan."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Siaran Notifikasi</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-600 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>VAPID Gateway Siap</span>
                </span>
            </div>
        </x-slot:actions>
    </x-heading-section>

    {{-- Notification Alert & Toast Bridge --}}
    <x-alert />

    {{-- Ringkasan Statistik Pelanggan Notifikasi --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card
            label="Total Perangkat Terdaftar"
            value="{{ $totalSubscribers }}"
            description="Browser & Gawai Aktif"
            icon="device-phone-mobile"
            color="amber"
        />

        <x-stat-card
            label="Perangkat Android & iOS"
            value="{{ $deviceBreakdown->whereIn('device_label', ['Android Browser', 'iOS Safari'])->sum('count') }}"
            description="Gawai Mobile Lapangan"
            icon="signal"
            color="blue"
        />

        <x-stat-card
            label="Riwayat Siaran Terkirim"
            value="{{ $recentBroadcasts->count() }}"
            description="Log Audit Siaran Resmi"
            icon="paper-airplane"
            color="emerald"
        />
    </div>

    {{-- Layout 2 Kolom: Form Pengiriman & Live Preview Gawai --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        {{-- Kolom Kiri: Form Composer Siaran --}}
        <div class="lg:col-span-7 rounded-2xl border border-zinc-200/80 bg-white p-5 sm:p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        Komposisi Pesan Siaran
                    </h3>
                    <p class="text-xs text-zinc-400">
                        Pesan akan dikirimkan ke Notification Tray browser pengguna
                    </p>
                </div>
                <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">
                    Web Push API
                </span>
            </div>

            <form wire:submit="sendBroadcast" class="space-y-4">
                {{-- Target Audiens --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Target Penerima:
                    </label>
                    <select
                        wire:model="targetRole"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                        <option value="">Semua Perangkat Terdaftar (Publik & Aparatur)</option>
                        <option value="judge">Khusus Dewan Juri</option>
                        <option value="committee">Khusus Panitia Pelaksana</option>
                        <option value="scout_group">Khusus Pembina Gugus Depan</option>
                    </select>
                </div>

                {{-- Judul Notifikasi --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Judul Notifikasi:
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="title"
                        placeholder="Contoh: Panggilan Masuk Arena Sesi 2 Pos Penjelajahan"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    @error('title')
                        <span class="mt-1 text-xs text-rose-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Isi Pesan --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Isi Pesan Notifikasi:
                    </label>
                    <textarea
                        wire:model.live.debounce.250ms="body"
                        rows="3"
                        placeholder="Tuliskan pesan instruksi singkat, pengingat jadwal, atau panggilan darurat..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    ></textarea>
                    @error('body')
                        <span class="mt-1 text-xs text-rose-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tautan Aksi & Tag Urgensi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Tautan URL Tujuan:
                        </label>
                        <input
                            type="text"
                            wire:model="actionUrl"
                            placeholder="/announcements atau /leaderboard"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                        />
                        @error('actionUrl')
                            <span class="mt-1 text-xs text-rose-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Kategori Notifikasi:
                        </label>
                        <select
                            wire:model="urgencyTag"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                        >
                            <option value="announcement">Pengumuman Biasa</option>
                            <option value="schedule">Pengingat Jadwal Sesi</option>
                            <option value="urgent">Peringatan Penting / Darurat</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800">
                    <span class="text-[11px] text-zinc-400">
                        Notifikasi terkirim otomatis dengan icon LT III 2026
                    </span>

                    <flux:button
                        type="submit"
                        icon="paper-airplane"
                        variant="primary"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Kirim Siaran Sekarang</span>
                        <span wire:loading>Mengirimkan...</span>
                    </flux:button>
                </div>
            </form>
        </div>

        {{-- Kolom Kanan: Pratinjau Tampilan Notifikasi (Mobile Device Mockup) --}}
        <div class="lg:col-span-5 space-y-4">
            <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50/60 p-5 dark:border-zinc-800 dark:bg-zinc-900/60 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                        Pratinjau Layar Notifikasi (Preview)
                    </h4>
                    <span class="rounded bg-zinc-200 px-1.5 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        Mobile Notification Tray
                    </span>
                </div>

                {{-- Banner Mockup Notifikasi Android / Desktop --}}
                <div class="rounded-2xl border border-zinc-300 bg-white p-4 shadow-md dark:border-zinc-700 dark:bg-zinc-900 space-y-2">
                    <div class="flex items-center justify-between text-[11px] text-zinc-400">
                        <div class="flex items-center gap-1.5">
                            <img src="{{ asset('logo.png') }}" class="size-4 object-contain" alt="LT3">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300">LT III 2026 &bull; Web Push</span>
                        </div>
                        <span>Baru saja</span>
                    </div>

                    <div class="flex items-start gap-3 pt-1">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                            <flux:icon name="bell" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $title ?: 'Judul notifikasi akan tampil di sini...' }}
                            </h5>
                            <p class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400 line-clamp-3 leading-relaxed">
                                {{ $body ?: 'Pratinjau teks pesan notifikasi yang akan diterima oleh perangkat panitia, juri, dan kontingen.' }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-zinc-400">
                        <span>Aksi Klik: <strong class="text-amber-600 dark:text-amber-400">{{ $actionUrl ?: '/' }}</strong></span>
                        <span class="uppercase font-mono font-bold">{{ $urgencyTag }}</span>
                    </div>
                </div>

                {{-- Statistik Perangkat Terdaftar --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 block">
                        Sebaran Perangkat Aktif
                    </span>
                    <div class="space-y-1.5">
                        @forelse($deviceBreakdown as $device)
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $device->device_label ?: 'Web Client' }}</span>
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ $device->count }} gawai</span>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400 italic">Belum ada perangkat yang mendaftar notifikasi.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Siaran Notifikasi Terakhir (Audit Log) --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white overflow-hidden shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
        <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                Riwayat Log Siaran Notifikasi ({{ $recentBroadcasts->count() }})
            </h3>
            <span class="text-[11px] text-zinc-400">Tercatat dalam audit trail</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-200 bg-zinc-50/80 font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <tr>
                        <th class="py-3 px-4">Waktu Pengiriman</th>
                        <th class="py-3 px-4">Pengirim</th>
                        <th class="py-3 px-4">Judul & Pesan</th>
                        <th class="py-3 px-4 text-center">Target Audiens</th>
                        <th class="py-3 px-4 text-center">Terkirim / Target</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                    @forelse($recentBroadcasts as $log)
                        @php
                            $vals = $log->new_values ?? [];
                            $payload = $vals['payload'] ?? [];
                        @endphp
                        <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 whitespace-nowrap text-zinc-500">
                                {{ $log->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                            </td>
                            <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $log->user?->name ?? 'Administrator' }}
                            </td>
                            <td class="py-3 px-4">
                                <strong class="text-zinc-900 dark:text-zinc-100 block">{{ $payload['title'] ?? '-' }}</strong>
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px] line-clamp-1">{{ $payload['body'] ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $vals['target_role'] ? ucfirst($vals['target_role']) : 'Semua Perangkat' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono">
                                <span class="text-emerald-600 font-bold">{{ $vals['sent_count'] ?? 0 }}</span>
                                <span class="text-zinc-400">/ {{ $vals['total_targeted'] ?? 0 }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-zinc-400 italic">
                                Belum ada riwayat siaran notifikasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
