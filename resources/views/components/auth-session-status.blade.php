@props([
    'status',
])

@if ($status)
    <x-alert type="success" :message="$status" :dismissible="true" {{ $attributes }} />
@endif
