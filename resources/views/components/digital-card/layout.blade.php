@props([
    'title' => 'ID Card B2',
    'bodyClass' => 'bg-[#0f172a]',
])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('resources/images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Standar Ukuran Kertas Cetak ID Card B2 (Khusus Printer Media) */
        @page {
            size: 79mm 126mm;
            margin: 0;
        }
        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center p-6 text-slate-900 print:bg-transparent print:p-0 {{ $bodyClass }}">
    {{ $slot }}
</body>
</html>
