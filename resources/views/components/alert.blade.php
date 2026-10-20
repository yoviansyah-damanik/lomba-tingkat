@props([
    'type' => null, // 'success', 'danger' / 'error', 'warning', 'info'
    'title' => null,
    'message' => null,
    'items' => null, // array of bullet point items
    'dismissible' => true,
    'autoSession' => true,
    'toast' => true, // set false untuk hanya menampilkan banner alert tanpa popup toast
])

@php
    // Kumpulkan seluruh pesan notifikasi jika mode otomatis aktif
    $alertsToRender = [];
    $sessionToasts = [];

    if ($type !== null || $message !== null || !empty($slot->toHtml()) || $title !== null) {
        $actualType = in_array($type, ['danger', 'error']) ? 'danger' : ($type ?? 'info');
        $alertsToRender[] = [
            'type' => $actualType,
            'title' => $title,
            'message' => $message,
            'slot' => $slot->toHtml(),
            'items' => is_array($items) ? $items : null,
        ];
    } elseif ($autoSession) {
        // Cek error validasi formulir
        if (isset($errors) && $errors->any()) {
            $alertsToRender[] = [
                'type' => 'danger',
                'title' => 'Terdapat Kesalahan Pengisian Formulir',
                'items' => $errors->all(),
            ];
            $sessionToasts[] = [
                'variant' => 'danger',
                'heading' => 'Terdapat Kesalahan Pengisian Formulir',
                'text' => $errors->first(),
            ];
        }

        // Cek pesan error dari flash session
        if (session('error')) {
            $alertsToRender[] = [
                'type' => 'danger',
                'title' => 'Terjadi Kesalahan',
                'message' => session('error'),
            ];
            $sessionToasts[] = [
                'variant' => 'danger',
                'heading' => 'Terjadi Kesalahan',
                'text' => session('error'),
            ];
        } elseif (session('danger')) {
            $alertsToRender[] = [
                'type' => 'danger',
                'title' => 'Perhatian',
                'message' => session('danger'),
            ];
            $sessionToasts[] = [
                'variant' => 'danger',
                'heading' => 'Perhatian',
                'text' => session('danger'),
            ];
        }

        // Cek pesan peringatan
        if (session('warning')) {
            $alertsToRender[] = [
                'type' => 'warning',
                'title' => 'Peringatan',
                'message' => session('warning'),
            ];
            $sessionToasts[] = [
                'variant' => 'warning',
                'heading' => 'Peringatan',
                'text' => session('warning'),
            ];
        }

        // Cek pesan sukses
        if (session('success')) {
            $alertsToRender[] = [
                'type' => 'success',
                'title' => 'Berhasil Disimpan',
                'message' => session('success'),
            ];
            $sessionToasts[] = [
                'variant' => 'success',
                'heading' => 'Berhasil Disimpan',
                'text' => session('success'),
            ];
        }

        // Cek status bawaan Laravel
        if (session('status') && !session('success')) {
            $statusMsg = session('status');
            $statusType = (is_string($statusMsg) && str_contains(strtolower($statusMsg), 'error')) ? 'danger' : 'success';
            $alertsToRender[] = [
                'type' => $statusType,
                'title' => 'Status Operasi',
                'message' => $statusMsg,
            ];
            $sessionToasts[] = [
                'variant' => $statusType,
                'heading' => 'Status Operasi',
                'text' => $statusMsg,
            ];
        }
    }
@endphp

{{-- Trigger Toast Component untuk Flash Session --}}
@if($toast && !empty($sessionToasts))
    <div x-data x-init="
        $nextTick(() => {
            @foreach($sessionToasts as $st)
                $dispatch('toast-show', {
                    duration: 5000,
                    slots: {
                        text: {{ Js::from($st['text']) }},
                        heading: {{ Js::from($st['heading']) }}
                    },
                    dataset: {
                        variant: {{ Js::from($st['variant']) }}
                    }
                });
            @endforeach
        });
    " class="hidden"></div>
@endif

@if(!empty($alertsToRender))
    <div {{ $attributes->merge(['class' => 'space-y-3 w-full']) }}>
        @foreach($alertsToRender as $item)
            @php
                $itemType = $item['type'];
                
                // Konfigurasi visual berdasarkan tipe alert
                $styles = match ($itemType) {
                    'success' => [
                        'container' => 'border-emerald-300/80 bg-emerald-50/90 text-emerald-950 dark:border-emerald-500/30 dark:bg-emerald-950/40 dark:text-emerald-100 shadow-emerald-500/5',
                        'icon_bg' => 'bg-emerald-500/15 text-emerald-700 dark:bg-emerald-500/25 dark:text-emerald-300',
                        'icon' => 'check-circle',
                        'accent' => 'from-emerald-500 to-teal-500',
                        'close' => 'text-emerald-700 hover:bg-emerald-200/50 dark:text-emerald-300 dark:hover:bg-emerald-900/50',
                    ],
                    'danger', 'error' => [
                        'container' => 'border-rose-300/80 bg-rose-50/90 text-rose-950 dark:border-rose-500/30 dark:bg-rose-950/40 dark:text-rose-100 shadow-rose-500/5',
                        'icon_bg' => 'bg-rose-500/15 text-rose-700 dark:bg-rose-500/25 dark:text-rose-300',
                        'icon' => 'x-circle',
                        'accent' => 'from-rose-500 to-red-500',
                        'close' => 'text-rose-700 hover:bg-rose-200/50 dark:text-rose-300 dark:hover:bg-rose-900/50',
                    ],
                    'warning' => [
                        'container' => 'border-amber-300/80 bg-amber-50/90 text-amber-950 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-100 shadow-amber-500/5',
                        'icon_bg' => 'bg-amber-500/15 text-amber-700 dark:bg-amber-500/25 dark:text-amber-300',
                        'icon' => 'exclamation-triangle',
                        'accent' => 'from-amber-500 to-orange-500',
                        'close' => 'text-amber-700 hover:bg-amber-200/50 dark:text-amber-300 dark:hover:bg-amber-900/50',
                    ],
                    default => [
                        'container' => 'border-sky-300/80 bg-sky-50/90 text-sky-950 dark:border-sky-500/30 dark:bg-sky-950/40 dark:text-sky-100 shadow-sky-500/5',
                        'icon_bg' => 'bg-sky-500/15 text-sky-700 dark:bg-sky-500/25 dark:text-sky-300',
                        'icon' => 'information-circle',
                        'accent' => 'from-sky-500 to-blue-500',
                        'close' => 'text-sky-700 hover:bg-sky-200/50 dark:text-sky-300 dark:hover:bg-sky-900/50',
                    ],
                };
            @endphp

            <div 
                x-data="{ show: true }" 
                x-show="show" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                role="alert" 
                data-alert-root
                data-alert-type="{{ $itemType }}"
                class="relative flex items-start gap-3.5 overflow-hidden rounded-2xl border p-4 shadow-sm backdrop-blur-xs transition-all {{ $styles['container'] }}"
            >
                {{-- Decorative Accent Bar --}}
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b {{ $styles['accent'] }}"></div>

                {{-- Status Icon --}}
                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $styles['icon_bg'] }}">
                    <flux:icon :name="$styles['icon']" class="size-5" />
                </div>

                {{-- Message Content --}}
                <div class="flex-1 pr-6 pt-0.5 text-xs sm:text-sm">
                    @if(!empty($item['title']))
                        <h4 class="font-bold tracking-tight text-current mb-0.5">
                            {{ $item['title'] }}
                        </h4>
                    @endif

                    @if(!empty($item['message']))
                        <div class="leading-relaxed opacity-90">
                            {{ $item['message'] }}
                        </div>
                    @endif

                    @if(!empty($item['items']))
                        <ul class="mt-1 list-disc pl-4 space-y-0.5 leading-relaxed opacity-90">
                            @foreach($item['items'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($item['slot']))
                        <div class="mt-1 leading-relaxed opacity-90">
                            {!! $item['slot'] !!}
                        </div>
                    @endif
                </div>

                {{-- Dismiss Button --}}
                @if($dismissible)
                    <button 
                        type="button" 
                        @click="show = false" 
                        onclick="this.closest('[data-alert-root]').remove()"
                        aria-label="Tutup notifikasi"
                        class="absolute right-2.5 top-2.5 flex size-7 items-center justify-center rounded-lg transition-colors {{ $styles['close'] }}"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        @endforeach
    </div>
@endif
