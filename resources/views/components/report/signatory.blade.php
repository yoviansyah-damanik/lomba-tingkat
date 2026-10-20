@props(['name' => null, 'nta' => null, 'space' => 56])

{{-- Blok penandatangan laporan PDF: ruang tanda tangan, nama (atau garis kosong), lalu NTA --}}
<div style="height: {{ $space }}px;"></div>
@if(filled($name))
    <strong style="text-decoration: underline;">{{ $name }}</strong>
@else
    <div style="width: 150px; margin: 0 auto; border-bottom: 1px solid #52525b; height: 12px;"></div>
@endif
<div class="muted" style="margin-top: {{ filled($name) ? 2 : 10 }}px;">NTA. {{ filled($nta) ? $nta : '..............................' }}</div>
