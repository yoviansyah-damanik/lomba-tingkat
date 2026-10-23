<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau PDF &mdash; {{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-screen flex-col bg-zinc-100 font-sans text-zinc-900 antialiased">
    {{-- Toolbar pratinjau: berlaku untuk seluruh laporan PDF --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 bg-white px-4 py-3 shadow-xs sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <span class="shrink-0 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">PRATINJAU PDF</span>
            <span class="truncate text-xs font-medium text-zinc-600">{{ $title }} (A4)</span>
        </div>

        <div class="flex items-center gap-2">
            @if($backUrl)
                <a href="{{ $backUrl }}" class="rounded-xl border border-zinc-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                    Kembali
                </a>
            @endif
            <a href="{{ $downloadUrl }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs transition-colors hover:bg-amber-700">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                <span>Unduh PDF</span>
            </a>
        </div>
    </div>

    {{-- Pratinjau dokumen --}}
    <iframe src="{{ $pdfUrl }}" title="Pratinjau PDF {{ $title }}" class="w-full flex-1 border-0 bg-zinc-200"></iframe>
</body>
</html>
