@props([
    'compact' => false,
])

@if ($compact)
    {{-- Tampilan Ringkas (untuk Dasbor Internal) --}}
    <div
        {{ $attributes->class('mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-2.5 px-4 py-3 text-xs text-zinc-600 dark:text-zinc-400 sm:flex-row sm:text-start') }}>
        <p class="font-normal text-center sm:text-start">
            &copy; 2026 <span
                class="font-semibold text-zinc-900 dark:text-zinc-200">{{ config('app.name', 'LT III Kwarcab Padangsidimpuan') }}</span>
            &mdash; Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan. <x-app-version />
        </p>
        <div class="flex items-center shrink-0" x-data="{
            isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
            installApp() {
                if (window.installPwaApp) {
                    window.installPwaApp();
                } else {
                    window.dispatchEvent(new CustomEvent('trigger-pwa-install'));
                }
            }
        }">
            <template x-if="!isStandalone">
                <flux:button icon="arrow-down-tray" variant="subtle" size="xs" x-on:click="installApp()">
                    Pasang Aplikasi
                </flux:button>
            </template>
        </div>
    </div>
@else
    {{-- Rich Footer Komprehensif (3 Kolom + Bottom Bar) --}}
    <div {{ $attributes->class('w-full bg-[#181824] text-zinc-300 dark:bg-zinc-950 border-t border-zinc-800/80') }}>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-12 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-10">

                {{-- KOLOM 1: Brand, Identitas & Penjelasan Portal --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('logo.png') }}" alt="Logo LT III" class="size-12 shrink-0 object-contain">
                        <div>
                            <span class="block text-base font-extrabold tracking-tight text-white">
                                {{ config('app.name', 'LT III Kwarcab Padangsidimpuan') }}
                            </span>
                            <span class="block text-xs font-semibold text-amber-400">
                                {{ config('app.description', 'Kwarcab Padangsidimpuan') }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-zinc-400 leading-relaxed max-w-md">
                        Situs ini adalah portal resmi penyelenggaraan Lomba Tingkat III (LT III) Kwartir Cabang Gerakan
                        Pramuka Kota Padangsidimpuan. Daftarkan pangkalan gudep, unggah data peserta regu penggalang,
                        pantau verifikasi berkas, dan cetak kartu QR peserta di sini.
                    </p>

                    <div class="pt-2">
                        <span class="text-[11px] text-zinc-500 block leading-relaxed max-w-md">
                            Dikelola oleh Panitia Pelaksana LT III Kwartir Cabang Gerakan Pramuka Kota
                            Padangsidimpuan Tahun 2026.
                        </span>
                    </div>
                </div>

                {{-- KOLOM 2: Pasang Aplikasi LT (Pengganti Situs Terkait) --}}
                <div class="lg:col-span-4 space-y-3.5" x-data="{
                    isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
                    installApp() {
                        if (window.installPwaApp) {
                            window.installPwaApp();
                        } else {
                            window.dispatchEvent(new CustomEvent('trigger-pwa-install'));
                        }
                    }
                }">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                        <flux:icon name="device-phone-mobile" class="size-4 text-amber-400" />
                        Pasang Aplikasi LT
                    </h3>

                    <div
                        class="rounded-2xl border border-amber-500/25 bg-gradient-to-br from-amber-950/40 via-zinc-900/90 to-zinc-900/90 p-5 shadow-lg backdrop-blur-xs space-y-3.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md ring-2 ring-amber-400/30">
                                    <flux:icon name="device-phone-mobile" class="size-6" />
                                </span>
                                <div>
                                    <h4 class="text-sm font-extrabold text-white tracking-tight">
                                        Aplikasi Resmi LT III
                                    </h4>
                                    <span class="text-xs text-amber-400 font-semibold">
                                        Kwarcab Padangsidimpuan
                                    </span>
                                </div>
                            </div>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-500/30">
                                <flux:icon name="bolt" class="size-3" />
                                Ringan
                            </span>
                        </div>

                        <p class="text-xs text-zinc-300 leading-relaxed">
                            Pasang aplikasi langsung di Layar Utama (*Home Screen*) ponsel atau desktop Anda untuk akses
                            cepat, stabil, dan hemat kuota.
                        </p>

                        <div class="space-y-1.5 text-[11px] text-zinc-400 border-t border-zinc-800/80 pt-2.5">
                            <div class="flex items-center gap-2">
                                <flux:icon name="check" class="size-3.5 text-emerald-400 shrink-0" />
                                <span>Akses satu ketukan langsung dari Layar Utama</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon name="check" class="size-3.5 text-emerald-400 shrink-0" />
                                <span>Mendukung Android, iPhone/iPad, dan Komputer</span>
                            </div>
                        </div>

                        <div class="pt-1">
                            <template x-if="isStandalone">
                                <div
                                    class="flex items-center justify-center gap-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 py-2.5 px-3 text-xs font-bold text-emerald-400">
                                    <flux:icon name="check-circle" class="size-4 text-emerald-400" />
                                    <span>Aplikasi Sudah Terpasang</span>
                                </div>
                            </template>

                            <template x-if="!isStandalone">
                                <div class="space-y-2">
                                    <flux:button icon="arrow-down-tray" variant="primary"
                                        class="w-full justify-center shadow-md shadow-amber-500/10 cursor-pointer"
                                        x-on:click="installApp()">
                                        Pasang Aplikasi Sekarang
                                    </flux:button>
                                    <span class="block text-[10px] text-center text-zinc-400">
                                        Pengguna iOS: pilih menu <em>Bagikan (Share)</em> &rarr; <em>Tambah ke Layar
                                            Utama</em>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- KOLOM 3: Alternatif (Sekretariat & Pusat Bantuan Penyelenggaraan) --}}
                <div class="lg:col-span-3 space-y-3.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400">
                        Sekretariat & Bantuan
                    </h3>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-4 space-y-3 backdrop-blur-sm">
                        <div class="flex items-start gap-2.5">
                            <flux:icon name="map-pin" class="size-4 shrink-0 text-amber-400 mt-0.5" />
                            <div class="text-xs">
                                <span class="font-bold text-zinc-200 block">Sanggar Pramuka Kwarcab</span>
                                <span class="text-[11px] text-zinc-400 leading-snug">
                                    Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan, Sumatera Utara.
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 pt-2 border-t border-zinc-800/80">
                            <flux:icon name="clock" class="size-4 shrink-0 text-amber-400 mt-0.5" />
                            <div class="text-xs">
                                <span class="font-bold text-zinc-200 block">Waktu Operasional Pos</span>
                                <span class="text-[11px] text-zinc-400 leading-snug">
                                    Pelayanan Berkas: 08.00 &ndash; 17.00 WIB
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-zinc-800/80">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400 block mb-1.5">
                                Akses Informasi:
                            </span>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <a href="{{ route('competitions') }}" wire:navigate
                                    class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-400 hover:text-amber-300 hover:underline">
                                    <span>Juknis Lomba &rarr;</span>
                                </a>
                                <a href="{{ route('guide') }}" wire:navigate
                                    class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-400 hover:text-amber-300 hover:underline">
                                    <span>Panduan &rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- BOTTOM BAR: Copyright & Pasang Aplikasi --}}
            <div
                class="mt-10 border-t border-zinc-800/80 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-400">
                <p class="text-center sm:text-start">
                    &copy; 2026 <span
                        class="font-semibold text-zinc-200">{{ config('app.name', 'LT III Kwarcab Padangsidimpuan') }}</span>
                    &mdash; Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan. <x-app-version />
                </p>
                <div class="flex items-center shrink-0" x-data="{
                    isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
                    installApp() {
                        if (window.installPwaApp) {
                            window.installPwaApp();
                        } else {
                            window.dispatchEvent(new CustomEvent('trigger-pwa-install'));
                        }
                    }
                }">
                    <template x-if="!isStandalone">
                        <flux:button icon="arrow-down-tray" variant="subtle" size="xs" x-on:click="installApp()"
                            class="text-amber-400 hover:text-amber-300">
                            Pasang Aplikasi di Layar Utama
                        </flux:button>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endif
