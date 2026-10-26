<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <x-public-header />

        <div class="relative grid flex-1 flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-neutral-900"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium gap-2.5" wire:navigate>
                    <x-app-logo-icon class="h-8 w-auto" />
                    <span>{{ config('app.name', 'Laravel') }}</span>
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto space-y-3">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                    <p class="text-[11px] text-neutral-400">
                        &copy; 2026 LT III Kwarcab Padangsidimpuan &mdash; Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan. <x-app-version />
                    </p>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    {{ $slot }}

                    <div class="text-center text-[11px] text-zinc-500 lg:hidden">
                        &copy; 2026 LT III Kwarcab Padangsidimpuan &mdash; Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan. <x-app-version />
                    </div>
                </div>
            </div>
        </div>

        <x-alert />

        @persist('toast')
            <flux:toast.group position="top end">
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
