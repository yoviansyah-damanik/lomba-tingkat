@props(['label', 'value' => null])

@php
    $displayValue = $value ?? (string) $slot;
    $titleText = strip_tags($displayValue);
@endphp

<tr class="py-[0.8mm] text-[6.5pt]">
    <td class="w-[38%] text-slate-500 font-bold uppercase tracking-wider align-top text-left whitespace-nowrap">
        {{ $label }}
    </td>
    <td class="w-[4%] align-top text-center">:</td>
    <td class="w-[58%] text-slate-800 font-bold align-top text-left max-w-0">
        <div class="truncate leading-normal" title="{{ $titleText }}">
            {{ $value ?? $slot }}
        </div>
    </td>
</tr>
