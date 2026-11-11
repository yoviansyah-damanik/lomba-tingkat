@props(['label', 'value' => null])

@php
    $displayValue = $value ?? (string) $slot;
    $titleText = strip_tags($displayValue);
@endphp

<tr class="text-[6pt] border-b border-[#d97706]/15 last:border-b-0">
    <td class="w-[38%] py-[0.35mm] text-[#92400e] font-extrabold uppercase tracking-wider align-top text-left whitespace-nowrap">
        {{ $label }}
    </td>
    <td class="w-[4%] py-[0.35mm] text-[#b45309] font-bold align-top text-center">:</td>
    <td class="w-[58%] py-[0.35mm] text-slate-900 font-bold align-top text-left max-w-0">
        <div class="truncate leading-snug" title="{{ $titleText }}">
            {{ $value ?? $slot }}
        </div>
    </td>
</tr>
