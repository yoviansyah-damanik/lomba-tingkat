{{-- Nomor versi aplikasi (config app.version, diatur lewat APP_VERSION) untuk footer seluruh halaman --}}
<span {{ $attributes->class('ms-1 inline-flex items-center rounded-md bg-zinc-500/10 px-1.5 py-0.5 align-middle font-mono text-[10px] font-semibold text-zinc-500 dark:text-zinc-400') }} title="Versi aplikasi">v{{ config('app.version') }}</span>
