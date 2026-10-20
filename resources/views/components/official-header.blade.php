@props([
    'title' => null,
    'subtitle' => null,
])

<div class="rounded-2xl border border-amber-200/40 bg-gradient-to-r from-amber-900/10 via-zinc-900/5 to-amber-800/10 p-5 shadow-sm dark:border-amber-500/20 dark:from-amber-950/40 dark:via-zinc-900 dark:to-zinc-900">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-amber-600/15 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-400/10 dark:text-amber-400">
                    Gerakan Pramuka
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Kwarcab Kota Padangsidimpuan
                </span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                {{ $title ?? 'Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan' }}
            </h1>
            <p class="text-xs text-zinc-600 dark:text-zinc-400 sm:text-sm">
                {{ $subtitle ?? 'Aplikasi Resmi Penyelenggaraan, Akreditasi, Presensi QR, dan Live Scoring LT III' }}
            </p>
        </div>

        @auth
            <div class="flex flex-wrap items-center gap-2 self-start md:self-center">
                @foreach(auth()->user()->roles as $role)
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/30 bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800 dark:border-amber-400/20 dark:bg-amber-950/60 dark:text-amber-300">
                        <span class="size-1.5 rounded-full bg-amber-500"></span>
                        {{ $role->label }}
                    </span>
                @endforeach
            </div>
        @endauth
    </div>
</div>
