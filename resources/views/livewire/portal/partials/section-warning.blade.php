{{-- Peringatan per bagian: daftar persyaratan yang belum terpenuhi pada bagian tersebut --}}
@php
    $items ??= [];
    $messages ??= [];
    $title ??= 'Bagian ini belum memenuhi persyaratan';
@endphp

@if(count($items) > 0 || count($messages) > 0)
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3.5 text-xs text-red-900 dark:border-red-500/30 dark:bg-red-950/30 dark:text-red-200" role="alert">
        <div class="flex items-center gap-2 font-bold">
            <flux:icon name="exclamation-triangle" class="size-4 shrink-0" />
            {{ $title }}
        </div>
        <ul class="mt-1.5 list-disc space-y-0.5 pl-6">
            @foreach($items as $item)
                <li><strong>{{ $item['title'] }}.</strong> {{ $item['description'] }}</li>
            @endforeach
            @foreach($messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif