<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Surat Izin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-zinc-100 p-4 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
    <main class="w-full max-w-md space-y-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
        @if ($permission)
            <div class="flex items-center gap-3">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                    <flux:icon name="check-badge" class="size-7" />
                </div>
                <div>
                    <h1 class="text-lg font-black">Surat Izin Sah</h1>
                    <p class="text-xs text-zinc-500">Tanda tangan digital Ketua Panitia Pelaksana LT III</p>
                </div>
            </div>

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Nomor</dt><dd class="text-right font-mono font-semibold">{{ $permission->letterNumber() }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Jenis Izin</dt><dd class="text-right font-semibold">{{ $permission->type->label() }}</dd></div>
                @if ($permission->scoutGroup)
                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">Gudep</dt><dd class="text-right font-semibold">{{ $permission->scoutGroup->school_name }}</dd></div>
                @endif
                @if ($permission->guest_name)
                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">Tamu</dt><dd class="text-right font-semibold">{{ $permission->guest_name }}</dd></div>
                @endif
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Diterbitkan</dt><dd class="text-right font-semibold">{{ $permission->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Status</dt><dd class="text-right font-semibold">{{ $permission->status === 'active' ? 'Belum kembali' : 'Selesai' }}</dd></div>
            </dl>

            <div>
                <p class="mb-1 text-xs font-bold uppercase tracking-wider text-zinc-500">Keterangan</p>
                <p class="text-sm">{{ $permission->reason }}</p>
            </div>

            @if ($permission->members->isNotEmpty())
                <ul class="space-y-1 rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-800/50">
                    @foreach ($permission->members as $member)
                        <li>{{ $member->displayName() }} <span class="text-xs text-zinc-500">({{ $member->participant_id ? 'Peserta' : 'Penanggung jawab' }})</span></li>
                    @endforeach
                </ul>
            @endif
        @else
            <div class="flex items-center gap-3">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300">
                    <flux:icon name="x-circle" class="size-7" />
                </div>
                <div>
                    <h1 class="text-lg font-black">Surat Tidak Ditemukan</h1>
                    <p class="text-xs text-zinc-500">QR Code tidak cocok dengan surat izin resmi mana pun.</p>
                </div>
            </div>
        @endif
    </main>
</body>
</html>
