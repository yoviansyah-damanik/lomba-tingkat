<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Presensi — LT-III 2026 Kwarcab Padangsidimpuan</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-break {
                page-break-after: always;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm 1.5cm 1.5cm 1.5cm;
            }
        }
    </style>
</head>
<body class="bg-zinc-100 font-sans text-zinc-900 antialiased min-h-screen">

    {{-- Toolbar Cetak Floating (Hanya tampil di layar) --}}
    <div class="no-print sticky top-0 z-50 flex items-center justify-between border-b border-zinc-200 bg-white/95 px-6 py-3.5 backdrop-blur-md shadow-xs">
        <div class="flex items-center gap-3">
            <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">
                PRATINJAU CETAK
            </span>
            <span class="text-xs text-zinc-600 font-medium">
                Rekapitulasi Presensi & Kehadiran • {{ $attendances->count() }} Orang Terdata
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button 
                type="button" 
                onclick="window.close()" 
                class="rounded-xl border border-zinc-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50"
            >
                Tutup Jendela
            </button>
            <button 
                type="button" 
                onclick="window.print()" 
                class="rounded-xl bg-amber-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-amber-700 transition-colors flex items-center gap-1.5"
            >
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar Presensi (PDF / Printer)</span>
            </button>
        </div>
    </div>

    {{-- Lembar Dokumen Fisik A4 --}}
    <div class="mx-auto my-6 max-w-[210mm] rounded-xl bg-white p-8 shadow-sm sm:p-12 print:my-0 print:max-w-none print:rounded-none print:p-0 print:shadow-none">
        
        {{-- KOP SURAT RESMI GERAKAN PRAMUKA --}}
        <div class="border-b-4 border-double border-zinc-900 pb-4 text-center">
            <div class="flex items-center justify-between gap-4">
                {{-- Logo Tunas Kelapa --}}
                <div class="size-16 shrink-0 flex items-center justify-center">
                    <svg class="size-14 text-amber-800" viewBox="0 0 100 100" fill="currentColor">
                        <path d="M50 5 C55 25, 75 35, 75 55 C75 75, 55 95, 50 95 C45 95, 25 75, 25 55 C25 35, 45 25, 50 5 Z M50 25 C45 35, 35 45, 35 55 C35 65, 45 75, 50 75 C55 75, 65 65, 65 55 C65 45, 55 35, 50 25 Z"/>
                    </svg>
                </div>

                <div class="flex-1">
                    <h1 class="text-base font-black uppercase tracking-wider text-zinc-900">
                        GERAKAN PRAMUKA KWARTIR CABANG KOTA PADANGSIDIMPUAN
                    </h1>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-zinc-800 mt-0.5">
                        PANITIA PELAKSANA LOMBA TINGKAT REGU PENGGALANG TIGA (LT-III)
                    </h2>
                    <p class="text-[11px] text-zinc-600 mt-1">
                        Sekretariat: Gedung Pramuka Kwarcab Kota Padangsidimpuan, Sumatera Utara • Kode Pos: 22711
                    </p>
                </div>

                {{-- Logo WOSM / Pramuka --}}
                <div class="size-16 shrink-0 flex items-center justify-center">
                    <svg class="size-14 text-purple-900" viewBox="0 0 100 100" fill="currentColor">
                        <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="6"/>
                        <path d="M50 15 L55 35 L75 35 L60 48 L65 70 L50 56 L35 70 L40 48 L25 35 L45 35 Z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Judul Dokumen & Metadata --}}
        <div class="mt-5 text-center">
            <h3 class="text-base font-black uppercase tracking-tight text-zinc-900 underline underline-offset-4">
                BERITA ACARA & REKAPITULASI PRESENSI KEGIATAN
            </h3>
            <div class="mt-1 text-xs text-zinc-700">
                @if($selectedSession)
                    Sesi: <strong class="text-zinc-900">{{ $selectedSession->title }}</strong>
                    @if($selectedSession->schedule?->arena_location)
                        &bull; Lokasi Pos: <strong class="text-zinc-900">{{ $selectedSession->schedule->arena_location }}</strong>
                    @endif
                @else
                    <strong>Seluruh Sesi Kegiatan & Pos Perlombaan LT-III</strong>
                @endif
            </div>
        </div>

        {{-- Ringkasan Statistik Presensi --}}
        <div class="mt-4 grid grid-cols-5 gap-2 rounded-lg border border-zinc-300 bg-zinc-50 p-2.5 text-center text-xs">
            <div>
                <span class="text-[10px] uppercase text-zinc-500 font-semibold">Total Terdata</span>
                <p class="font-bold text-sm text-zinc-900">{{ $stats['total'] }}</p>
            </div>
            <div>
                <span class="text-[10px] uppercase text-zinc-500 font-semibold">Hadir Tepat</span>
                <p class="font-bold text-sm text-emerald-800">{{ $stats['present'] }}</p>
            </div>
            <div>
                <span class="text-[10px] uppercase text-zinc-500 font-semibold">Terlambat</span>
                <p class="font-bold text-sm text-amber-800">{{ $stats['late'] }}</p>
            </div>
            <div>
                <span class="text-[10px] uppercase text-zinc-500 font-semibold">Izin / Disp</span>
                <p class="font-bold text-sm text-blue-800">{{ $stats['excused'] }}</p>
            </div>
            <div>
                <span class="text-[10px] uppercase text-zinc-500 font-semibold">Alpa / Absen</span>
                <p class="font-bold text-sm text-rose-800">{{ $stats['absent'] }}</p>
            </div>
        </div>

        {{-- Tabel Rekapitulasi Presensi --}}
        <div class="mt-4">
            <table class="w-full border-collapse border border-zinc-400 text-left text-[11px]">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-800">
                        <th class="border border-zinc-400 px-2 py-1.5 text-center w-8">No</th>
                        <th class="border border-zinc-400 px-2 py-1.5 w-24">Nomor ID</th>
                        <th class="border border-zinc-400 px-2 py-1.5">Nama Lengkap</th>
                        <th class="border border-zinc-400 px-2 py-1.5">Peran / Jabatan</th>
                        <th class="border border-zinc-400 px-2 py-1.5">Pangkalan / Regu</th>
                        <th class="border border-zinc-400 px-2 py-1.5 w-20 text-center">Waktu</th>
                        <th class="border border-zinc-400 px-2 py-1.5 w-24 text-center">Status</th>
                        <th class="border border-zinc-400 px-2 py-1.5">Catatan / Paraf</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $idx => $att)
                        <tr class="{{ $idx % 2 === 1 ? 'bg-zinc-50' : 'bg-white' }}">
                            <td class="border border-zinc-300 px-2 py-1 text-center font-mono">
                                {{ $idx + 1 }}
                            </td>
                            <td class="border border-zinc-300 px-2 py-1 font-mono font-semibold">
                                {{ $att->entity_id_number }}
                            </td>
                            <td class="border border-zinc-300 px-2 py-1 font-bold text-zinc-900">
                                {{ $att->entity_name }}
                            </td>
                            <td class="border border-zinc-300 px-2 py-1">
                                {{ $att->entity_role }}
                            </td>
                            <td class="border border-zinc-300 px-2 py-1">
                                {{ $att->entity_group }}
                            </td>
                            <td class="border border-zinc-300 px-2 py-1 text-center font-mono">
                                {{ $att->scanned_at?->format('H:i') }} WIB
                            </td>
                            <td class="border border-zinc-300 px-2 py-1 text-center font-semibold">
                                @if($att->status === 'present')
                                    HADIR
                                @elseif($att->status === 'late')
                                    TERLAMBAT
                                @elseif($att->status === 'excused')
                                    IZIN
                                @elseif($att->status === 'absent')
                                    ALPA
                                @else
                                    {{ strtoupper($att->status) }}
                                @endif
                            </td>
                            <td class="border border-zinc-300 px-2 py-1 text-zinc-600">
                                {{ $att->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="border border-zinc-300 px-4 py-8 text-center text-zinc-500 italic">
                                Belum ada data presensi yang tercatat untuk kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tanggal Cetak & Kolom Pengesahan Tanda Tangan --}}
        <div class="mt-8 text-xs text-zinc-800 break-inside-avoid">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-zinc-500">
                    <div>Dicetak pada: {{ now()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm:ss') }} WIB</div>
                    <div>Petugas Pencetak: {{ auth()->user()?->name ?? 'Administrator' }} ({{ auth()->user()?->roleLabel() ?? 'Staf' }})</div>
                    <div class="mt-1 font-mono">Dokumen Otentik Sistem Digital LT-III Kwarcab Padangsidimpuan</div>
                </div>

                <div class="w-72 text-center">
                    <p>Padangsidimpuan, {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y') }}</p>
                    <p class="font-bold mt-1">Petugas Pos / Koordinator Presensi,</p>
                    <div class="h-20"></div>
                    <p class="font-bold underline text-zinc-900">{{ auth()->user()?->name ?? '..................................' }}</p>
                    <p class="text-[11px] text-zinc-600">NTA / ID: {{ auth()->user()?->id_number ?? '..................................' }}</p>
                </div>
            </div>

            <div class="mt-6 border-t border-zinc-300 pt-3 text-center text-[10px] text-zinc-500">
                Lembar rekapitulasi presensi ini merupakan bagian dari berkas resmi penyelenggaraan Lomba Tingkat Regu Penggalang Tiga (LT-III) Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan.
            </div>
        </div>

    </div>

</body>
</html>
