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
    <x-public-header />

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
