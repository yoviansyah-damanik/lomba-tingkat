<div class="w-full shrink-0 rounded-[2.4mm] border border-[#d97706]/30 bg-amber-50/45 px-[2.8mm] py-[1.2mm] shadow-2xs">
    <table {{ $attributes->merge(['class' => 'w-full table-fixed border-collapse text-[8pt]']) }}>
        {{ $slot }}
    </table>
</div>
