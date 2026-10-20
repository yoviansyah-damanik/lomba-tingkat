<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk Sistem &mdash; {{ config('app.name', 'LT III Kwarcab Padangsidimpuan') }}</title>
    <meta name="description" content="Masuk ke Sistem Informasi Terpadu {{ config('app.name') }} {{ config('app.description') }}. Portal pendaftaran mandiri, validasi presensi, dan live scoring.">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    {{-- Styles & Scripts via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased selection:bg-amber-500 selection:text-white dark:bg-zinc-950 dark:text-zinc-100 flex flex-col font-sans">

    {{-- Header Navigasi Atas --}}
    <x-public-header />

    {{-- Main Showcase & Login Container --}}
    <main class="flex-1 relative flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        {{-- Ambient Backdrop Lights --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden -z-10">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 size-[36rem] rounded-full bg-amber-500/10 blur-[100px] dark:bg-amber-600/10"></div>
            <div class="absolute -bottom-32 right-10 size-[28rem] rounded-full bg-amber-700/10 blur-[100px] dark:bg-amber-700/10"></div>
        </div>

        <div class="w-full max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            {{-- KOLOM KIRI (Desktop): Identitas Acara, Fitur Sistem & Nilai Kepramukaan --}}
            <div class="hidden lg:flex lg:col-span-6 flex-col justify-center space-y-7">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/80 bg-amber-50/90 px-3.5 py-1 text-xs font-semibold text-amber-800 shadow-xs dark:border-amber-500/30 dark:bg-amber-950/60 dark:text-amber-300">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Portal Terpadu Lomba Tingkat III 2026</span>
                    </div>

                    <h1 class="mt-4 text-3xl xl:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white leading-tight">
                        Satu Pintu Layanan Digital Kontingen & Aparatur Kegiatan
                    </h1>

                    <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Dirancang khusus untuk memfasilitasi administrasi mandiri pangkalan sekolah, pengelolaan akreditasi peserta, hingga transparansi penilaian dewan juri secara real-time.
                    </p>
                </div>

                {{-- Feature Highlights --}}
                <div class="space-y-3.5">
                    <div class="group flex items-start gap-3.5 rounded-2xl border border-zinc-200/80 bg-white/70 p-3.5 shadow-xs backdrop-blur-sm transition-all hover:border-amber-300 hover:bg-amber-50/30 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-amber-500/30">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 shadow-xs">
                            <flux:icon name="user-group" class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Pangkalan Gugus Depan</h2>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">
                                Pendaftaran regu putra/putri, kelengkapan berkas syarat peserta, dan pencetakan Form 01 resmi.
                            </p>
                        </div>
                    </div>

                    <div class="group flex items-start gap-3.5 rounded-2xl border border-zinc-200/80 bg-white/70 p-3.5 shadow-xs backdrop-blur-sm transition-all hover:border-amber-300 hover:bg-amber-50/30 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-amber-500/30">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 shadow-xs">
                            <flux:icon name="qr-code" class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Panitia Pelaksana & Absensi Pos</h2>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">
                                Verifikasi berkas, pemindaian QR Code kartu peserta terenkripsi berbasis waktu server resmi WIB.
                            </p>
                        </div>
                    </div>

                    <div class="group flex items-start gap-3.5 rounded-2xl border border-zinc-200/80 bg-white/70 p-3.5 shadow-xs backdrop-blur-sm transition-all hover:border-amber-300 hover:bg-amber-50/30 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-amber-500/30">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 shadow-xs">
                            <flux:icon name="scale" class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Dewan Juri & Blind Scoring</h2>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">
                                Input skor mata lomba objektif tanpa nomor gudep serta siaran langsung peringkat perolehan medali.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN: Formulir Login Card --}}
            <div class="lg:col-span-6 w-full max-w-md mx-auto">
                <div class="relative overflow-hidden rounded-3xl border border-zinc-200/90 bg-white/95 p-6 sm:p-8 shadow-xl shadow-zinc-900/5 backdrop-blur-xl dark:border-zinc-800/90 dark:bg-zinc-900/95 dark:shadow-black/40">
                    {{-- Ambient Top Card Line --}}
                    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700"></div>

                    {{-- Card Header --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-900 dark:bg-amber-950/70 dark:text-amber-300">
                                <flux:icon name="shield-check" class="size-3.5 text-amber-600 dark:text-amber-400" />
                                <span>Autentikasi Akun</span>
                            </span>

                            <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
                                Sesi Terenkripsi
                            </span>
                        </div>

                        <h2 class="text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                            Masuk ke Sistem
                        </h2>
                        <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                            Silakan masukkan email dan kata sandi akun pangkalan Gudep, panitia, atau juri.
                        </p>
                    </div>

                    {{-- Notification Alerts (Status & Errors) --}}
                    <x-alert class="mb-4" />

                    {{-- Login Form --}}
                    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                        @csrf

                        {{-- Email Field --}}
                        <div>
                            <flux:input
                                name="email"
                                label="Alamat Email Terdaftar"
                                :value="old('email')"
                                type="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="nama@sekolah.sch.id / email..."
                                icon="envelope"
                            />
                        </div>

                        {{-- Password Field --}}
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Kata Sandi
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-800 hover:underline dark:text-amber-400 dark:hover:text-amber-300">
                                        Lupa kata sandi?
                                    </a>
                                @endif
                            </div>
                            <flux:input
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                icon="lock-closed"
                                viewable
                            />
                        </div>

                        {{-- Remember Me Option --}}
                        <div class="pt-0.5 flex items-center justify-between">
                            <flux:checkbox name="remember" label="Ingat saya di perangkat ini" :checked="old('remember')" />
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-2">
                            <flux:button
                                type="submit"
                                variant="primary"
                                icon="arrow-right-start-on-rectangle"
                                data-test="login-button"
                                class="w-full justify-center shadow-lg shadow-amber-600/25"
                            >
                                Masuk ke Dasbor
                            </flux:button>
                        </div>
                    </form>

                    {{-- Opsi Pendaftaran Gudep (Pemisah Estetik) --}}
                    <div class="mt-6 pt-5 border-t border-zinc-200/80 dark:border-zinc-800">
                        <div class="rounded-2xl border border-amber-300/70 bg-gradient-to-br from-amber-50/70 via-white to-amber-100/40 p-4 dark:border-amber-500/30 dark:from-amber-950/30 dark:via-zinc-900 dark:to-zinc-900">
                            <div class="flex items-start gap-3">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-xs">
                                    <flux:icon name="building-library" class="size-4.5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate">
                                            Pendaftaran Pangkalan Gudep
                                        </h3>
                                        <span class="rounded bg-amber-200/80 px-1.5 py-0.5 text-[9px] font-extrabold uppercase text-amber-900 dark:bg-amber-900/80 dark:text-amber-200">
                                            Sekolah
                                        </span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-zinc-600 dark:text-zinc-400 leading-snug">
                                        Belum memiliki akun pangkalan SMP/MTs? Daftarkan Gugus Depan Anda sekarang.
                                    </p>
                                    <a
                                        href="{{ route('register.scout-group') }}"
                                        wire:navigate
                                        class="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-bold text-zinc-800 shadow-xs transition hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-amber-500/50 dark:hover:bg-zinc-700"
                                    >
                                        <span>Daftar Gugusdepan Baru</span>
                                        <flux:icon name="arrow-right" class="size-3.5" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tautan Panduan --}}
                    <div class="mt-4">
                        <flux:button href="{{ route('guide') }}" wire:navigate icon="book-open-text" variant="subtle" size="sm" class="w-full">
                            Baca Panduan Pendaftaran &amp; Penggunaan
                        </flux:button>
                    </div>

                    {{-- Helpdesk Footnote --}}
                    <div class="mt-4 text-center">
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Kendala akun atau lupa akses? Hubungi <span class="font-semibold text-zinc-700 dark:text-zinc-300">Sekretariat Panitia LT III</span>.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- Footer Konsisten --}}
    <footer class="mt-auto border-t border-zinc-200/60 bg-white/60 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/60">
        <x-official-footer :compact="true" />
    </footer>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
