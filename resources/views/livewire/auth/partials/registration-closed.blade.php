{{-- Notice: Pendaftaran Ditutup --}}
<div class="rounded-2xl border border-rose-200 bg-white p-8 text-center shadow-sm dark:border-rose-900/50 dark:bg-zinc-900">
    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
        <flux:icon name="lock-closed" class="size-7" />
    </div>
    <h2 class="mt-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
        Pendaftaran Akun Gugus Depan Telah Ditutup
    </h2>
    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 max-w-lg mx-auto leading-relaxed">
        Batas waktu pendaftaran akun pangkalan untuk Lomba Tingkat III Kwarcab Padangsidimpuan telah berakhir pada {{ $event?->registration_end_at?->translatedFormat('d F Y, H:i') ?? 'jadwal resmi' }} WIB.
    </p>
    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
        <flux:button href="{{ route('login') }}" wire:navigate variant="primary" icon="arrow-right-end-on-rectangle">
            Masuk ke Dasbor Kontingen
        </flux:button>
        <flux:button href="{{ route('home') }}" wire:navigate icon="home">
            Kembali ke Beranda
        </flux:button>
    </div>
</div>
