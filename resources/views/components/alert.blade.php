@props([
    'type' => null, // 'success', 'danger' / 'error', 'warning', 'info'
    'title' => null,
    'message' => null,
    'items' => null, // array of bullet point items
    'dismissible' => true,
    'autoSession' => true,
])

{{-- Notifikasi mengambang (toast) saja, tanpa box inline. Prop $dismissible dipertahankan demi kompatibilitas. --}}
@php
    $toasts = [];

    // Notifikasi eksplisit via props / slot
    if ($type !== null || $message !== null || ! empty($slot->toHtml()) || $title !== null) {
        $variant = in_array($type, ['danger', 'error']) ? 'danger' : ($type ?? 'info');
        $text = $message
            ?: trim(strip_tags($slot->toHtml()))
            ?: (is_array($items) ? implode(' • ', array_slice($items, 0, 3)) : '');

        $toasts[] = [
            'variant' => $variant,
            'heading' => $title ?? match ($variant) {
                'success' => 'Berhasil',
                'danger' => 'Perhatian',
                'warning' => 'Peringatan',
                default => 'Informasi',
            },
            'text' => $text,
        ];
    } elseif ($autoSession) {
        // Error validasi formulir
        if (isset($errors) && $errors->any()) {
            $all = $errors->all();
            $extra = count($all) - 3;
            $toasts[] = [
                'variant' => 'danger',
                'heading' => 'Terdapat Kesalahan Pengisian Formulir',
                'text' => implode(' • ', array_slice($all, 0, 3)).($extra > 0 ? " (+{$extra} kesalahan lain)" : ''),
                'duration' => 8000,
            ];
        }

        // Flash session
        if (session('error')) {
            $toasts[] = ['variant' => 'danger', 'heading' => 'Terjadi Kesalahan', 'text' => session('error')];
        } elseif (session('danger')) {
            $toasts[] = ['variant' => 'danger', 'heading' => 'Perhatian', 'text' => session('danger')];
        }

        if (session('warning')) {
            $toasts[] = ['variant' => 'warning', 'heading' => 'Peringatan', 'text' => session('warning')];
        }

        if (session('success')) {
            $toasts[] = ['variant' => 'success', 'heading' => 'Berhasil Disimpan', 'text' => session('success')];
        }

        // Status bawaan Laravel
        if (session('status') && ! session('success')) {
            $statusMsg = session('status');
            $toasts[] = [
                'variant' => (is_string($statusMsg) && str_contains(strtolower($statusMsg), 'error')) ? 'danger' : 'success',
                'heading' => 'Status Operasi',
                'text' => $statusMsg,
            ];
        }
    }

    $toasts = array_values(array_filter($toasts, fn ($t) => filled($t['text'] ?? null)));
@endphp

@if (! empty($toasts))
    <div x-data x-init="
        $nextTick(() => {
            @foreach ($toasts as $t)
                $dispatch('toast-show', {
                    duration: {{ $t['duration'] ?? 5000 }},
                    slots: {
                        text: {{ Js::from($t['text']) }},
                        heading: {{ Js::from($t['heading']) }}
                    },
                    dataset: {
                        variant: {{ Js::from($t['variant']) }}
                    }
                });
            @endforeach
        });
    " class="hidden" data-alert-toast></div>
@endif
