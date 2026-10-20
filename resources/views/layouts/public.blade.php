<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Beranda' }} &mdash; LT III Kwarcab Padangsidimpuan</title>
    <meta name="description" content="Portal Resmi Lomba Tingkat III (LT III) Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan. Informasi kegiatan, jadwal, juknis, presensi QR, dan live scoring.">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    {{-- PWA Manifest & Mobile Web App Meta --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#d97706">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="LT III 2026">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    {{-- Styles & Scripts via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased selection:bg-amber-500 selection:text-white dark:bg-zinc-950 dark:text-zinc-100 flex flex-col font-sans">
    {{-- Header Navigasi Publik --}}
    <header class="sticky top-0 z-50 border-b border-amber-200/50 bg-white/80 backdrop-blur-md dark:border-amber-500/20 dark:bg-zinc-900/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            {{-- Logo Brand --}}
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 transition-transform hover:scale-[1.01]">
                <img src="{{ asset('logo.png') }}" alt="Logo LT III" class="size-10 object-contain drop-shadow-sm">
                <div>
                    <span class="block text-sm font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-base">
                        {{ config('app.name', 'LT III Kwarcab Padangsidimpuan') }}
                    </span>
                    <span class="block text-[11px] font-medium text-amber-700 dark:text-amber-400">
                        {{ config('app.description', 'Gerakan Pramuka Kota Padangsidimpuan') }}
                    </span>
                </div>
            </a>

            {{-- Navigasi Desktop (Livewire 4 wire:navigate) --}}
            <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('home') }}" wire:navigate class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('home') ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    Beranda
                </a>
                <a href="{{ route('announcements') }}" wire:navigate class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('announcements*') ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    Pengumuman
                </a>
                <a href="{{ route('competitions') }}" wire:navigate class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('competitions') ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    Mata Lomba & Juknis
                </a>
                <a href="{{ route('schedules') }}" wire:navigate class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('schedules') ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    Jadwal
                </a>
                <a href="{{ route('participants') }}" wire:navigate class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('participants') ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    Peserta
                </a>
                <a href="{{ route('leaderboard') }}" wire:navigate class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('leaderboard') ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    Klasemen
                </a>
                <a href="{{ route('guide') }}" wire:navigate class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('guide') ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    Panduan
                </a>
            </nav>

            {{-- Tombol Aksi Masuk / Data User --}}
            <div class="flex items-center gap-2.5">
                @auth
                    @php
                        $user = auth()->user();
                        $roleLabel = $user->roleLabel();
                        $initials = $user->initials();
                    @endphp

                    {{-- Dropdown Data Akun Login (Desktop) --}}
                    <div class="relative hidden md:block" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen"
                                type="button"
                                id="user-menu-button"
                                class="flex items-center gap-2.5 rounded-full border border-amber-200/80 bg-white/90 py-1 pl-1.5 pr-3 shadow-xs hover:border-amber-300 dark:border-amber-500/20 dark:bg-zinc-800/90 dark:hover:border-amber-500/40 transition-all cursor-pointer">
                            <div class="flex size-7 items-center justify-center rounded-full bg-gradient-to-br from-amber-600 to-amber-700 text-[11px] font-bold text-white shadow-xs">
                                {{ $initials ?: 'U' }}
                            </div>
                            <div class="text-left leading-tight">
                                <span class="block text-xs font-bold text-zinc-900 dark:text-zinc-100 max-w-[130px] truncate">
                                    {{ $user->name }}
                                </span>
                                <span class="block text-[10px] font-medium text-amber-700 dark:text-amber-400 max-w-[130px] truncate">
                                    {{ $roleLabel }}
                                </span>
                            </div>
                            <svg class="size-3.5 text-zinc-400 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown Panel Desktop --}}
                        <div x-show="userMenuOpen"
                             @click.away="userMenuOpen = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 mt-2 w-60 rounded-2xl border border-zinc-200 bg-white p-2 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 z-50">

                            <div class="px-3 py-2.5 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $user->name }}</div>
                                <div class="text-[11px] text-zinc-500 truncate">{{ $user->email }}</div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                        {{ $roleLabel }}
                                    </span>
                                </div>
                            </div>

                            <div class="py-1">
                                {{-- Tombol ke Dasbor --}}
                                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-amber-50 hover:text-amber-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-amber-300 transition-colors">
                                    <flux:icon name="squares-2x2" class="size-4 text-amber-600" />
                                    <span>Halaman Dasbor</span>
                                </a>

                                {{-- Tombol ke Profil --}}
                                <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">
                                    <flux:icon name="user" class="size-4 text-zinc-500" />
                                    <span>Pengaturan Profil</span>
                                </a>
                            </div>

                            <div class="pt-1 border-t border-zinc-100 dark:border-zinc-800">
                                {{-- Tombol Logout --}}
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40 transition-colors cursor-pointer text-left">
                                        <flux:icon name="arrow-right-start-on-rectangle" class="size-4 text-red-500" />
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Belum login (Desktop): Hanya tampilkan tombol Masuk --}}
                    <a href="{{ route('login') }}" wire:navigate class="hidden md:inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm shadow-amber-600/20 transition-all hover:brightness-105">
                        <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                        <span>Masuk</span>
                    </a>
                @endauth

                {{-- Tombol Dark Mode Switcher --}}
                <x-theme-toggle />

                {{-- Mobile Menu Button --}}
                <div class="md:hidden" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex size-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 dark:border-zinc-800 dark:text-zinc-300 cursor-pointer" aria-label="Menu Utama">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak x-transition class="absolute inset-x-0 top-full border-b border-zinc-200 bg-white p-4 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 z-50">
                        @auth
                            {{-- Akun Terautentikasi di Mobile Menu --}}
                            <div class="mb-3 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/60">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-600 to-amber-700 font-bold text-white text-xs shadow-xs">
                                        {{ $initials ?? 'U' }}
                                    </div>
                                    <div class="flex-1 min-w-0 text-xs">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ auth()->user()->name }}</div>
                                        <div class="text-[11px] text-zinc-500 truncate">{{ auth()->user()->email }}</div>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                {{ $roleLabel ?? auth()->user()->roleLabel() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-t border-zinc-200/70 dark:border-zinc-700/60 grid grid-cols-2 gap-2">
                                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center gap-1.5 rounded-xl bg-amber-600 px-3 py-2 text-xs font-semibold text-white shadow-xs hover:bg-amber-700">
                                        <flux:icon name="squares-2x2" class="size-3.5" />
                                        <span>Dasbor</span>
                                    </a>
                                    <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        <flux:icon name="user" class="size-3.5" />
                                        <span>Profil</span>
                                    </a>
                                </div>

                                <div class="mt-2">
                                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50/70 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400 cursor-pointer">
                                            <flux:icon name="arrow-right-start-on-rectangle" class="size-3.5 text-red-500" />
                                            <span>Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- Tombol Masuk / Daftar di Mobile Menu --}}
                            <div class="mb-3 space-y-2">
                                <a href="{{ route('login') }}" wire:navigate class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 py-2.5 text-xs font-semibold text-white shadow-sm shadow-amber-600/20 hover:brightness-105">
                                    <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                                    <span>Masuk ke Akun</span>
                                </a>
                                <a href="{{ route('register.scout-group') }}" wire:navigate class="flex items-center justify-center gap-2 rounded-xl border border-amber-300 bg-amber-50/70 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
                                    <flux:icon name="building-library" class="size-4 text-amber-600 dark:text-amber-400" />
                                    <span>Daftar Gugusdepan Baru</span>
                                </a>
                            </div>
                        @endauth

                        <nav class="flex flex-col gap-2 text-sm font-medium">
                            <a href="{{ route('home') }}" wire:navigate class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">Beranda</a>
                            <a href="{{ route('announcements') }}" wire:navigate class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">Pengumuman</a>
                            <a href="{{ route('competitions') }}" wire:navigate class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">Mata Lomba & Juknis</a>
                            <a href="{{ route('schedules') }}" wire:navigate class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">Jadwal Kegiatan</a>
                            <a href="{{ route('participants') }}" wire:navigate class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">Peserta Kontingen</a>
                            <a href="{{ route('leaderboard') }}" wire:navigate class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">Klasemen</a>
                            <a href="{{ route('guide') }}" wire:navigate class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">Panduan Pendaftaran</a>
                        </nav>

                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1">
        <x-pwa-install-banner />
        {{ $slot }}
    </main>

    {{-- Official Footer --}}
    <footer class="mt-auto">
        <x-official-footer />
    </footer>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @if (session('success') || session('error') || session('danger') || session('warning') || session('status'))
        @php
            $pubSuccess = session()->pull('success');
            $pubError = session()->pull('error') ?: session()->pull('danger');
            $pubWarning = session()->pull('warning');
            $pubStatus = session()->pull('status');
            $pubText = $pubSuccess ?: $pubError ?: $pubWarning ?: $pubStatus;
            $pubVariant = $pubSuccess ? 'success' : ($pubError ? 'danger' : ($pubWarning ? 'warning' : 'info'));
            $pubHeading = $pubSuccess ? 'Berhasil' : ($pubError ? 'Perhatian' : ($pubWarning ? 'Peringatan' : 'Informasi'));
        @endphp
        <div x-data x-init="
            $nextTick(() => {
                $dispatch('toast-show', {
                    duration: 5000,
                    slots: {
                        text: {{ Js::from($pubText) }},
                        heading: {{ Js::from($pubHeading) }}
                    },
                    dataset: {
                        variant: {{ Js::from($pubVariant) }}
                    }
                });
            });
        " class="hidden"></div>
    @endif

    @fluxScripts

    {{-- PWA Service Worker & Push Notification Integration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('LT3 PWA ServiceWorker registered with scope: ', registration.scope);
                }).catch(function(err) {
                    console.warn('LT3 PWA ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
