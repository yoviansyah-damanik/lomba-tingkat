<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pendaftaran Berhasil &mdash; {{ config('app.name', 'LT III Kwarcab Padangsidimpuan') }}</title>
    <meta name="description" content="Akun pangkalan Gugus Depan berhasil dibuat. Simpan kredensial login dan amankan akun Anda.">

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

    {{-- Main Success Content --}}
    <main class="flex-1 relative flex items-center justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        {{-- Ambient Backdrop Lights --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden -z-10">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 size-[36rem] rounded-full bg-emerald-500/10 blur-[100px] dark:bg-emerald-600/10"></div>
            <div class="absolute -bottom-32 right-10 size-[28rem] rounded-full bg-amber-500/10 blur-[100px] dark:bg-amber-600/10"></div>
        </div>

        <div class="w-full max-w-2xl mx-auto"
            x-data="{
                copiedUsername: false,
                copiedPassword: false,
                copiedAll: false,
                showPassword: false,
                usernameText: '{{ $credentials['username'] }}',
                passwordText: '{{ $credentials['password'] }}',
                copyText(text, type) {
                    navigator.clipboard.writeText(text);
                    if (type === 'username') {
                        this.copiedUsername = true;
                        setTimeout(() => this.copiedUsername = false, 2000);
                    } else if (type === 'password') {
                        this.copiedPassword = true;
                        setTimeout(() => this.copiedPassword = false, 2000);
                    } else if (type === 'all') {
                        this.copiedAll = true;
                        setTimeout(() => this.copiedAll = false, 2500);
                    }
                }
            }">

            <div class="relative overflow-hidden rounded-3xl border border-emerald-300/80 bg-white p-6 sm:p-10 shadow-2xl shadow-emerald-500/10 dark:border-emerald-500/30 dark:bg-zinc-900">
                {{-- Ambient Top Line --}}
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-amber-500"></div>

                {{-- Status Badge & Icon --}}
                <div class="text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-lg shadow-emerald-500/30">
                        <flux:icon name="check-circle" class="size-9" />
                    </div>

                    <div class="mt-5 inline-flex items-center gap-1.5 rounded-full border border-emerald-300/80 bg-emerald-50 px-3.5 py-1 text-xs font-bold text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Pendaftaran Pangkalan Berhasil</span>
                    </div>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                        Selamat Datang, {{ $credentials['school_name'] }}!
                    </h1>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 max-w-lg mx-auto">
                        Akun pangkalan Gugus Depan Anda telah resmi diterbitkan oleh sistem. Silakan simpan kredensial login di bawah ini sebelum melanjutkan.
                    </p>
                </div>

                {{-- Box Kredensial Terbuat --}}
                <div class="mt-8 rounded-2xl border border-zinc-200 bg-gradient-to-br from-zinc-50 to-white p-5 sm:p-6 dark:border-zinc-800 dark:from-zinc-900/90 dark:to-zinc-900 shadow-xs">
                    <div class="flex items-center justify-between border-b border-zinc-200/80 pb-3 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <flux:icon name="key" class="size-4.5 text-amber-600 dark:text-amber-400" />
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">
                                Kredensial Login Akses Portal
                            </h2>
                        </div>
                        <flux:button
                            size="xs"
                            variant="subtle"
                            icon="clipboard-document"
                            @click="copyText('ID: {{ $credentials['gudep_id'] }}\nUsername: {{ $credentials['username'] }}\nPassword: {{ $credentials['password'] }}\nPangkalan: {{ $credentials['school_name'] }}', 'all')"
                        >
                            <span x-text="copiedAll ? 'Tersalin Semua!' : 'Salin Semua Data'"></span>
                        </flux:button>
                    </div>

                    <div class="mt-4 space-y-3.5">
                        {{-- ID Gudep Resmi --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 rounded-xl bg-white p-3 border border-zinc-200/90 dark:bg-zinc-800/80 dark:border-zinc-700">
                            <div>
                                <span class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">ID Gudep (Nomor Registrasi)</span>
                                <span class="font-mono text-sm font-bold text-amber-700 dark:text-amber-400">{{ $credentials['gudep_id'] }}</span>
                            </div>
                            <span class="self-start sm:self-auto rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold uppercase text-amber-900 dark:bg-amber-950/70 dark:text-amber-300">
                                Unik Resmi
                            </span>
                        </div>

                        {{-- Username --}}
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-white p-3 border border-zinc-200/90 dark:bg-zinc-800/80 dark:border-zinc-700">
                            <div class="flex-1 min-w-0">
                                <span class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Username Login</span>
                                <span class="font-mono text-sm font-extrabold text-zinc-900 dark:text-zinc-100 truncate block">{{ $credentials['username'] }}</span>
                            </div>
                            <flux:button
                                size="xs"
                                variant="subtle"
                                icon="clipboard"
                                @click="copyText(usernameText, 'username')"
                            >
                                <span x-text="copiedUsername ? 'Tersalin!' : 'Salin'"></span>
                            </flux:button>
                        </div>

                        {{-- Kata Sandi Otomatis --}}
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-amber-50/70 p-3 border border-amber-200 dark:bg-amber-950/30 dark:border-amber-900/60">
                            <div class="flex-1 min-w-0">
                                <span class="block text-[11px] font-semibold text-amber-800 dark:text-amber-300">Kata Sandi Bawaan Sistem</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-sm font-extrabold tracking-wider text-amber-900 dark:text-amber-200"
                                        x-text="showPassword ? passwordText : '••••••••••'">
                                    </span>
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="text-xs text-amber-700 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-200 focus:outline-hidden">
                                        <flux:icon name="eye" class="size-3.5" x-show="!showPassword" />
                                        <flux:icon name="eye-slash" class="size-3.5" x-show="showPassword" x-cloak />
                                    </button>
                                </div>
                            </div>
                            <flux:button
                                size="xs"
                                variant="primary"
                                icon="clipboard"
                                @click="copyText(passwordText, 'password')"
                            >
                                <span x-text="copiedPassword ? 'Tersalin!' : 'Salin'"></span>
                            </flux:button>
                        </div>
                    </div>
                </div>

                {{-- Alert Penting Ubah Password --}}
                <div class="mt-6 rounded-2xl border border-amber-300 bg-amber-50/90 p-4.5 dark:border-amber-800/80 dark:bg-amber-950/40">
                    <div class="flex items-start gap-3">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white shadow-xs">
                            <flux:icon name="shield-exclamation" class="size-4" />
                        </div>
                        <div class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed">
                            <strong class="font-bold block text-amber-950 dark:text-amber-100 mb-0.5">
                                PENTING: Segera Ubah Kata Sandi Akun Anda!
                            </strong>
                            Kata sandi di atas digenerate secara otomatis oleh sistem. Demi keamanan data kontingen, Anda sangat disarankan untuk langsung mengubahnya ke kata sandi pribadi Anda.
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-6 border-t border-zinc-200/80 dark:border-zinc-800">
                    <flux:button
                        href="{{ route('security.edit') }}"
                        variant="primary"
                        icon="key"
                        class="w-full sm:w-auto"
                    >
                        Ubah Kata Sandi Sekarang
                    </flux:button>

                    <flux:button
                        href="{{ route('scout-group.index') }}"
                        variant="subtle"
                        icon="arrow-right"
                        class="w-full sm:w-auto"
                    >
                        Lanjut ke Dasbor Kontingen
                    </flux:button>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer Bawah Sederhana --}}
    <footer class="py-6 text-center text-xs text-zinc-500 dark:text-zinc-500">
        &copy; {{ date('Y') }} Panitia Pelaksana Lomba Tingkat III Kwarcab Gerakan Pramuka Kota Padangsidimpuan.
    </footer>

    @fluxScripts
</body>
</html>
