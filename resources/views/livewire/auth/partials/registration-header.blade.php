{{-- Header Badge & Title --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200/80 pb-6 dark:border-zinc-800">
    <div>
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-300 mb-2">
            <span class="size-1.5 rounded-full bg-amber-600"></span>
            Formulir Registrasi Mandiri Pangkalan
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">
            Registrasi Akun Pangkalan Gugus Depan
        </h1>
        <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 max-w-2xl">
            Daftarkan akun resmi pangkalan sekolah untuk memperoleh akses ke Dasbor Kontingen LT III Kwarcab Padangsidimpuan.
        </p>
    </div>

    {{-- Status Batas Waktu --}}
    @if ($event && $event->registration_end_at)
        <div class="rounded-2xl border {{ $isRegistrationOpen ? 'border-amber-200 bg-amber-50/70 dark:border-amber-900/40 dark:bg-amber-950/30' : 'border-rose-200 bg-rose-50/70 dark:border-rose-900/40 dark:bg-rose-950/30' }} p-3.5 shadow-xs flex items-center gap-3 self-start sm:self-auto">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $isRegistrationOpen ? 'bg-amber-600 text-white' : 'bg-rose-600 text-white' }} font-bold shadow-xs">
                <flux:icon name="calendar" class="size-4.5" />
            </div>
            <div class="text-xs">
                <span class="block font-semibold uppercase tracking-wider text-[10px] {{ $isRegistrationOpen ? 'text-amber-800 dark:text-amber-300' : 'text-rose-800 dark:text-rose-300' }}">
                    Batas Waktu Registrasi Pangkalan
                </span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $event->registration_end_at->translatedFormat('d F Y, H:i') }} WIB
                </span>
            </div>
        </div>
    @endif
</div>
