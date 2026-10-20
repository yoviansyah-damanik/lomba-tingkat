<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <x-public-header />

        <div class="bg-background flex flex-1 flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>

                <div class="text-center text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                    &copy; 2026 LT III Kwarcab Padangsidimpuan &mdash; Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan.
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
