@props(['name' => null, 'nta' => null, 'space' => 56, 'qr' => null, 'showNta' => true])

{{-- Blok penandatangan seluruh cetakan: ruang tanda tangan (opsional QR), nama (atau garis kosong), lalu NTA --}}
<div style="height: {{ $space }}px; text-align: center;">
    @if($qr)
        <img src="{{ $qr }}" alt="QR Tanda Tangan" style="width: 52px; height: 52px; margin-top: 2px;">
    @endif
</div>
@if(filled($name))
    <strong style="text-decoration: underline;">{{ $name }}</strong>
@else
    <div style="width: 150px; margin: 0 auto; border-bottom: 1px solid #52525b; height: 12px;"></div>
@endif
@if($showNta)
    <div style="margin-top: {{ filled($name) ? 2 : 10 }}px; font-size: 8px; color: #71717a;">NTA. {{ filled($nta) ? $nta : '..............................' }}</div>
@endif
