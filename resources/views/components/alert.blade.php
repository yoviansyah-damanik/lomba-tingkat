@props([
    'type' => null, // 'success', 'danger' / 'error', 'warning', 'info'
    'title' => null,
    'message' => null,
    'items' => null, // array of bullet point items
    'dismissible' => true,
    'autoSession' => true,
    'position' => 'top-right', // top-right, top-left, bottom-left, bottom-right, center
    'duration' => 5000,
])

{{-- Notifikasi mengambang (toast) saja, tanpa box inline. Posisi diatur lewat prop $position. --}}
@php
    $toasts = [];

    // Normalisasi posisi: "top right", "top_right", "TOP-RIGHT" -> "top-right"
    $positionKey = str_replace([' ', '_'], '-', strtolower(trim((string) $position)));
    $positionClasses = match ($positionKey) {
        'top-left' => 'top-4 left-4 items-start',
        'bottom-left' => 'bottom-4 left-4 items-start',
        'bottom-right' => 'bottom-4 right-4 items-end',
        'center' => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 items-center',
        default => 'top-4 right-4 items-end',
    };

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
            // Kode status bawaan Fortify diterjemahkan ke pesan yang ramah
            $statusMsg = match ($statusMsg) {
                'verification-link-sent' => 'Tautan verifikasi baru telah dikirim ke alamat email Anda.',
                default => $statusMsg,
            };
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
    <div data-alert-toast data-position="{{ $positionKey }}"
        class="pointer-events-none fixed z-[100] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2 print:hidden {{ $positionClasses }}">
        @foreach ($toasts as $t)
            @php
                // Warna latar, batas, ikon, dan teks sesuai jenis notifikasi
                $style = match ($t['variant']) {
                    'success' => ['icon' => 'check-circle', 'card' => 'border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-950', 'iconColor' => 'text-emerald-600 dark:text-emerald-400', 'heading' => 'text-emerald-900 dark:text-emerald-100', 'text' => 'text-emerald-800 dark:text-emerald-200'],
                    'danger' => ['icon' => 'x-circle', 'card' => 'border-rose-300 bg-rose-50 dark:border-rose-700 dark:bg-rose-950', 'iconColor' => 'text-rose-600 dark:text-rose-400', 'heading' => 'text-rose-900 dark:text-rose-100', 'text' => 'text-rose-800 dark:text-rose-200'],
                    'warning' => ['icon' => 'exclamation-triangle', 'card' => 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950', 'iconColor' => 'text-amber-600 dark:text-amber-400', 'heading' => 'text-amber-900 dark:text-amber-100', 'text' => 'text-amber-800 dark:text-amber-200'],
                    default => ['icon' => 'information-circle', 'card' => 'border-sky-300 bg-sky-50 dark:border-sky-700 dark:bg-sky-950', 'iconColor' => 'text-sky-600 dark:text-sky-400', 'heading' => 'text-sky-900 dark:text-sky-100', 'text' => 'text-sky-800 dark:text-sky-200'],
                };
            @endphp

            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, {{ (int) ($t['duration'] ?? $duration) }})"
                x-show="show" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                role="alert" data-variant="{{ $t['variant'] }}"
                class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border p-3 shadow-lg {{ $style['card'] }}">
                <flux:icon :name="$style['icon']" class="mt-0.5 size-5 shrink-0 {{ $style['iconColor'] }}" />

                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold {{ $style['heading'] }}">{{ $t['heading'] }}</div>
                    <div class="mt-0.5 text-xs leading-relaxed {{ $style['text'] }}">{{ $t['text'] }}</div>
                </div>

                @if ($dismissible)
                    <flux:button size="xs" variant="subtle" icon="x-mark" x-on:click="show = false" aria-label="Tutup notifikasi" />
                @endif
            </div>
        @endforeach
    </div>
@endif
