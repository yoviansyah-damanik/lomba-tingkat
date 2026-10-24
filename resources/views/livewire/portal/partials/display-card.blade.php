{{-- Kartu layar horizontal: desain khusus halaman ini (bukan replika ID card cetak); identitas dan QR diperbesar untuk ditunjukkan ke petugas --}}
@php
    $themes = [
        'boys' => ['panel' => 'from-blue-700 to-blue-900', 'accent' => 'text-blue-700 dark:text-blue-300'],
        'girls' => ['panel' => 'from-rose-600 to-rose-900', 'accent' => 'text-rose-700 dark:text-rose-300'],
        'chaperone-male' => ['panel' => 'from-emerald-600 to-emerald-900', 'accent' => 'text-emerald-700 dark:text-emerald-300'],
        'chaperone-female' => ['panel' => 'from-purple-600 to-purple-900', 'accent' => 'text-purple-700 dark:text-purple-300'],
        'staff' => ['panel' => 'from-red-700 to-red-950', 'accent' => 'text-red-700 dark:text-red-300'],
        'judge' => ['panel' => 'from-amber-600 to-amber-900', 'accent' => 'text-amber-700 dark:text-amber-300'],
    ];
    $style = $themes[$display['theme']] ?? $themes['boys'];
@endphp

<article class="mx-auto flex w-full max-w-4xl flex-col overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900 md:flex-row">
    {{-- Kiri: identitas pemilik --}}
    <section class="relative flex flex-col items-center justify-between gap-5 overflow-hidden bg-linear-to-br {{ $style['panel'] }} px-6 py-7 text-center text-white md:w-[22rem] md:shrink-0">
        <div class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-12 -left-8 size-36 rounded-full bg-black/10"></div>

        <div class="relative flex w-full items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="size-8 shrink-0 rounded-md bg-white/15 p-0.5">
                <span class="text-[11px] font-bold uppercase tracking-widest text-white/90">LT III 2026</span>
            </div>
            <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider backdrop-blur-sm">{{ $display['category'] }}</span>
        </div>

        <div class="relative flex flex-col items-center">
            <div class="size-32 overflow-hidden rounded-full border-4 border-white/90 bg-white/20 shadow-lg">
                @if ($display['photo'])
                    <img src="{{ asset('storage/'.$display['photo']) }}" alt="{{ $display['name'] }}" class="size-full object-cover">
                @else
                    <div class="flex size-full items-center justify-center text-4xl font-black text-white/80">{{ strtoupper(mb_substr($display['name'], 0, 2)) }}</div>
                @endif
            </div>

            <h2 class="mt-4 text-2xl font-black uppercase leading-tight">{{ $display['name'] }}</h2>

            @if ($display['badge'])
                <span class="mt-2 inline-block rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-900">{{ $display['badge'] }}</span>
            @endif
        </div>

        <div class="relative w-full rounded-2xl bg-black/20 px-4 py-3 backdrop-blur-sm">
            <p class="font-mono text-2xl font-extrabold tracking-wider">{{ $display['id_number'] }}</p>
            <p class="font-mono text-sm font-semibold text-white/80">NTA: {{ $display['scout_id'] ?: '-' }}</p>
        </div>
    </section>

    {{-- Kanan: QR code besar dan rincian --}}
    <section class="flex min-w-0 flex-1 flex-col justify-between gap-5 p-6">
        <div class="text-center">
            <div class="mx-auto w-fit rounded-3xl border-2 border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-700">
                <img src="{{ $qrDataUri }}" alt="QR Code {{ $display['name'] }}" class="size-60 sm:size-64">
            </div>
            <p class="mt-3 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Tunjukkan QR Code ini kepada petugas untuk verifikasi dan presensi</p>
            <p class="mt-1 break-all font-mono text-[11px] font-bold {{ $style['accent'] }}">{{ $token }}</p>
        </div>

        <dl class="space-y-1.5 rounded-2xl border border-zinc-100 bg-zinc-50/70 px-4 py-3 text-sm dark:border-zinc-800 dark:bg-zinc-800/40">
            @foreach ($display['rows'] as $label => $value)
                <div class="flex justify-between gap-4">
                    <dt class="shrink-0 text-zinc-500 dark:text-zinc-400">{{ $label }}</dt>
                    <dd class="text-right font-semibold text-zinc-900 dark:text-zinc-100">{{ $value ?: '-' }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</article>
