<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100 flex flex-col font-sans">
        <x-public-header />

        <main class="flex-1 flex flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                {{ $slot }}
            </div>
        </main>

        <div class="text-center text-xs text-zinc-500 dark:text-zinc-400 pb-6">
            &copy; 2026 LT III Kwarcab Padangsidimpuan &mdash; Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan. <x-app-version />
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
