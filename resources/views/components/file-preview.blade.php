@props([
    'path',
    'title' => 'Pratinjau Berkas',
    'height' => 'h-72',
])

@php
    $url = asset('storage/'.$path);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true);
    $isPdf = $ext === 'pdf';
@endphp

{{-- Pratinjau tersemat (gambar/PDF) dengan tombol buka layar penuh via <x-file-viewer> --}}
<div {{ $attributes->class(['relative overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900', $height]) }}>
    @if ($isImage)
        <img src="{{ $url }}" alt="{{ $title }}" loading="lazy" class="size-full object-contain">
    @elseif ($isPdf)
        <iframe src="{{ $url }}#view=FitH" title="{{ $title }}" loading="lazy" class="size-full"></iframe>
    @else
        <div class="flex size-full flex-col items-center justify-center gap-1 text-center text-zinc-400">
            <flux:icon name="document" class="size-8" />
            <span class="text-[11px]">Pratinjau tidak tersedia untuk jenis berkas ini.</span>
        </div>
    @endif

    <div class="absolute right-2 top-2">
        <x-file-viewer-trigger :path="$path" :title="$title" label="Layar Penuh" icon="arrows-pointing-out" variant="filled" />
    </div>
</div>
