@props(['digits' => [], 'bg' => 'bg-[#1e3a8a]'])

{{-- Lencana NPP di sisi kiri foto, sepasang dengan lencana PINRU/WAPINRU di sisi kanan:
     angka NPP saja (tanpa label) disusun vertikal dengan ukuran besar --}}
<div class="npp-badge w-[6.5mm] h-full shrink-0 flex flex-col items-center justify-between py-[1.2mm] {{ $bg }} text-white font-black leading-none" title="Nomor Pendaftaran Peserta (NPP)">
    <div class="flex flex-col items-center justify-center flex-1 gap-[1mm] text-[15pt] tracking-normal">
        @foreach($digits as $digit)
            <span>{{ $digit }}</span>
        @endforeach
    </div>
</div>
