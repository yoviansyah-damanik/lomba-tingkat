@props(['npp' => null, 'hide' => false])

@if ($npp && ! $hide)
    <span {{ $attributes->class('inline-flex items-center rounded-md bg-zinc-100 px-1.5 py-0.5 font-mono text-[11px] font-extrabold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100') }} title="Nomor Pendaftaran Peserta">{{ $npp }}</span>
@endif
