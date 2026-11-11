<div class="w-full shrink-0 rounded-[2mm] border border-[#d97706]/30 bg-amber-50/45 px-[2mm] py-[0.8mm] shadow-2xs">
    <table {{ $attributes->merge(['class' => 'w-full table-fixed border-collapse text-[7pt]']) }}>
        {{ $slot }}
    </table>
</div>
