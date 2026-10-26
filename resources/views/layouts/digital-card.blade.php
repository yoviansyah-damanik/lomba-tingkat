<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ID Card B2' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col items-center p-6 text-slate-900 print:hidden {{ $bodyClass ?? 'bg-[#0f172a] bg-[radial-gradient(ellipse_at_top,#1e293b_0%,#0f172a_65%)]' }}">
    {{ $slot }}
</body>
</html>
