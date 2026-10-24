@props([
    'path',
    'title' => 'Pratinjau Berkas',
    'label' => 'Lihat',
    'icon' => 'eye',
    'size' => 'xs',
    'variant' => 'subtle',
])

{{-- Tombol pembuka modal <x-file-viewer>; berkas diambil dari disk public --}}
<flux:button
    type="button"
    :size="$size"
    :variant="$variant"
    :icon="$icon"
    {{ $attributes }}
    x-on:click="$dispatch('open-file-viewer', {{ \Illuminate\Support\Js::from(['url' => asset('storage/'.$path), 'title' => $title]) }})"
>
    {{ $label }}
</flux:button>
