@props(['value' => null, 'label' => 'ID'])

{{-- Lencana ID (peserta, bindamping, gudep) berformat monospace agar konsisten di seluruh tampilan --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded border border-amber-200 bg-amber-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-amber-700 dark:border-amber-800/50 dark:bg-amber-950/40 dark:text-amber-400']) }}>
    <span class="font-sans font-semibold text-amber-600/70 dark:text-amber-400/70">{{ $label }}</span>
    {{ $value ?: '-' }}
</span>
