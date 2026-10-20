<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Hasil Akhir & Klasemen Resmi — LT-III Kwarcab Padangsidimpuan 2026</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
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

    {{-- Toolbar Cetak Floating (Hanya Tampil di Layar) --}}
    <div class="no-print sticky top-0 z-50 flex items-center justify-between border-b border-zinc-200 bg-white/95 px-6 py-3.5 backdrop-blur-md shadow-xs">
        <div class="flex items-center gap-3">
            <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">
                DOKUMEN RESMI
            </span>
            <span class="text-xs text-zinc-600 font-medium">
                Berita Acara Penetapan Juara Umum & Klasemen Akhir LT-III 2026
            </span>
        </div>
        <div class="flex items-center gap-2">
            <flux:button 
                type="button" 
                variant="subtle" 
                size="sm"
                onclick="window.close()" 
            >
                Tutup Jendela
            </flux:button>
            <flux:button 
                type="button" 
                icon="printer"
                variant="primary" 
                size="sm"
                onclick="window.print()" 
            >
                Cetak Berita Acara (PDF / Printer)
            </flux:button>
        </div>
    </div>

    {{-- Lembar Dokumen Fisik A4 --}}
    <div class="mx-auto my-6 max-w-[210mm] bg-white p-10 shadow-lg sm:rounded-2xl print:m-0 print:max-w-none print:p-0 print:shadow-none space-y-6 text-xs text-zinc-800 leading-relaxed">
        
        {{-- Kop Surat Resmi Gerakan Pramuka --}}
        <div class="border-b-2 border-double border-zinc-900 pb-4 text-center relative">
            <div class="flex items-center justify-between px-2">
                <img src="{{ asset('logo.png') }}" class="size-16 object-contain" alt="Logo Pramuka">
                <div class="flex-1 px-4">
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-zinc-700">
                        Gerakan Pramuka Kwartir Cabang Kota Padangsidimpuan
                    </h3>
                    <h1 class="text-base font-extrabold uppercase tracking-wide text-zinc-900 mt-0.5">
                        LOMBA TINGKAT REGU PRAMUKA PENGGALANG TIGA (LT-III)
                    </h1>
                    <p class="text-[11px] text-zinc-600 mt-0.5">
                        Tahun 2026 &bull; Kota Padangsidimpuan, Sumatera Utara
                    </p>
                </div>
                <img src="{{ asset('logo.png') }}" class="size-16 object-contain opacity-0 print:opacity-0" alt="Placeholder Spacer">
            </div>
        </div>

        {{-- Judul Berita Acara --}}
        <div class="text-center space-y-1">
            <h2 class="text-sm font-bold uppercase tracking-wider underline">
                BERITA ACARA PENETAPAN HASIL AKHIR & JUARA UMUM
            </h2>
            <p class="text-[11px] text-zinc-500 font-mono">
                Nomor: BA-LT3/KWARCAB-PSP/{{ date('Y') }}/{{ str_pad(rand(1, 99), 3, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        {{-- Teks Pembuka / Konsiderans --}}
        <p class="text-justify text-xs">
            Pada hari ini, <strong>{{ $generatedAt->translatedFormat('l') }}</strong>, tanggal <strong>{{ $generatedAt->translatedFormat('d F Y') }}</strong>, bertempat di Bumi Perkemahan LT-III Kwarcab Gerakan Pramuka Kota Padangsidimpuan, Dewan Juri bersama Panitia Pelaksana telah menyelesaikan seluruh rangkaian penilaian cabang perlombaan serta melakukan rekapitulasi perolehan prestasi kontingen secara cermat, transparan, dan dapat dipertanggungjawabkan.
        </p>

        {{-- Ringkasan Juara Umum Kategori Putra & Putri --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Juara Putra --}}
            <div class="rounded-xl border border-zinc-300 p-3 space-y-2">
                <div class="border-b border-zinc-200 pb-1.5 flex items-center justify-between">
                    <span class="font-bold uppercase tracking-wide text-zinc-900 text-[11px]">
                        Regu Putra Berprestasi Tinggi
                    </span>
                    <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-bold text-amber-900">
                        Juara Umum Putra
                    </span>
                </div>
                @if($boysStandings->isNotEmpty())
                    @php $topBoy = $boysStandings->first(); @endphp
                    <div class="space-y-1">
                        <strong class="text-xs text-zinc-900 block">{{ $topBoy['school_name'] }}</strong>
                        <p class="text-[11px] text-zinc-500">Gudep: {{ $topBoy['group_number'] }} &bull; Kwarran: {{ $topBoy['kwarran_name'] }}</p>
                        <div class="flex items-center gap-2 pt-1 text-[11px]">
                            <span class="font-bold text-amber-600">{{ $topBoy['gold'] }} Emas</span>
                            <span>&bull;</span>
                            <span class="font-bold text-zinc-500">{{ $topBoy['silver'] }} Perak</span>
                            <span>&bull;</span>
                            <span class="font-bold text-amber-800">{{ $topBoy['bronze'] }} Perunggu</span>
                            <span>&bull;</span>
                            <span class="font-bold text-zinc-900">Poin: {{ $topBoy['total_points'] }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-[11px] text-zinc-400 italic">Belum ada data penilaian putra.</p>
                @endif
            </div>

            {{-- Juara Putri --}}
            <div class="rounded-xl border border-zinc-300 p-3 space-y-2">
                <div class="border-b border-zinc-200 pb-1.5 flex items-center justify-between">
                    <span class="font-bold uppercase tracking-wide text-zinc-900 text-[11px]">
                        Regu Putri Berprestasi Tinggi
                    </span>
                    <span class="rounded bg-rose-100 px-1.5 py-0.5 text-[9px] font-bold text-rose-900">
                        Juara Umum Putri
                    </span>
                </div>
                @if($girlsStandings->isNotEmpty())
                    @php $topGirl = $girlsStandings->first(); @endphp
                    <div class="space-y-1">
                        <strong class="text-xs text-zinc-900 block">{{ $topGirl['school_name'] }}</strong>
                        <p class="text-[11px] text-zinc-500">Gudep: {{ $topGirl['group_number'] }} &bull; Kwarran: {{ $topGirl['kwarran_name'] }}</p>
                        <div class="flex items-center gap-2 pt-1 text-[11px]">
                            <span class="font-bold text-amber-600">{{ $topGirl['gold'] }} Emas</span>
                            <span>&bull;</span>
                            <span class="font-bold text-zinc-500">{{ $topGirl['silver'] }} Perak</span>
                            <span>&bull;</span>
                            <span class="font-bold text-amber-800">{{ $topGirl['bronze'] }} Perunggu</span>
                            <span>&bull;</span>
                            <span class="font-bold text-zinc-900">Poin: {{ $topGirl['total_points'] }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-[11px] text-zinc-400 italic">Belum ada data penilaian putri.</p>
                @endif
            </div>
        </div>

        {{-- Tabel Klasemen Akhir Lengkap --}}
        <div class="space-y-2">
            <h4 class="font-bold text-xs uppercase tracking-wider text-zinc-800">
                Daftar Peringkat & Perolehan Medali Kontingen
            </h4>
            <table class="w-full text-left text-xs border border-zinc-300">
                <thead class="bg-zinc-100 font-bold uppercase tracking-wider border-b border-zinc-300 text-[10px]">
                    <tr>
                        <th class="py-2 px-2 text-center border-r border-zinc-300 w-10">Rank</th>
                        <th class="py-2 px-3 border-r border-zinc-300">Pangkalan / Gugus Depan</th>
                        <th class="py-2 px-2 text-center border-r border-zinc-300">Gudep</th>
                        <th class="py-2 px-2 border-r border-zinc-300">Kwarran</th>
                        <th class="py-2 px-2 text-center border-r border-zinc-300">Emas</th>
                        <th class="py-2 px-2 text-center border-r border-zinc-300">Perak</th>
                        <th class="py-2 px-2 text-center border-r border-zinc-300">Perunggu</th>
                        <th class="py-2 px-2 text-center border-r border-zinc-300 font-extrabold">Poin</th>
                        <th class="py-2 px-3 text-center">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($standings as $index => $st)
                        @php
                            $rank = $index + 1;
                            $predicate = match ($rank) {
                                1 => 'Regu Prestasi Tinggi',
                                2 => 'Regu Prestasi Baik',
                                3 => 'Regu Prestasi Cukup',
                                default => 'Peringkat ' . $rank,
                            };
                        @endphp
                        <tr class="{{ $rank <= 3 ? 'font-semibold bg-amber-50/40' : '' }}">
                            <td class="py-1.5 px-2 text-center border-r border-zinc-300 font-mono">{{ $rank }}</td>
                            <td class="py-1.5 px-3 border-r border-zinc-300 font-bold text-zinc-900">{{ $st['school_name'] }}</td>
                            <td class="py-1.5 px-2 text-center border-r border-zinc-300 font-mono text-[11px]">{{ $st['group_number'] }}</td>
                            <td class="py-1.5 px-2 border-r border-zinc-300 text-[11px]">{{ $st['kwarran_name'] }}</td>
                            <td class="py-1.5 px-2 text-center border-r border-zinc-300 font-bold text-amber-600">{{ $st['gold'] }}</td>
                            <td class="py-1.5 px-2 text-center border-r border-zinc-300 font-bold text-zinc-500">{{ $st['silver'] }}</td>
                            <td class="py-1.5 px-2 text-center border-r border-zinc-300 font-bold text-amber-800">{{ $st['bronze'] }}</td>
                            <td class="py-1.5 px-2 text-center border-r border-zinc-300 font-extrabold text-zinc-900">{{ $st['total_points'] }}</td>
                            <td class="py-1.5 px-3 text-center text-[10px] uppercase font-bold">{{ $predicate }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-zinc-400 italic">
                                Belum ada data kontingen terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Penutup --}}
        <p class="text-justify text-xs">
            Demikian Berita Acara ini dibuat dan disahkan dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya sebagai dasar penerbitan Surat Keputusan Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan mengenai Kontingen Berprestasi Tinggi yang berhak mewakili Kota Padangsidimpuan ke Lomba Tingkat IV (LT-IV) Kwartir Daerah Sumatera Utara.
        </p>

        {{-- Tanda Tangan Aparatur Kegiatan --}}
        <div class="pt-6 grid grid-cols-3 gap-6 text-center text-xs">
            <div class="space-y-16">
                <p>Mengetahui,<br><strong>Ketua Panitia Pelaksana LT-III</strong></p>
                <div>
                    <strong class="border-b border-zinc-800 pb-0.5 block font-bold uppercase">( Panitia Pelaksana )</strong>
                    <span class="text-[10px] text-zinc-500 font-mono">NTA. 02.21.0001</span>
                </div>
            </div>

            <div class="space-y-16">
                <p>Ditetapkan Oleh,<br><strong>Ketua Dewan Juri LT-III</strong></p>
                <div>
                    <strong class="border-b border-zinc-800 pb-0.5 block font-bold uppercase">( Ketua Dewan Juri )</strong>
                    <span class="text-[10px] text-zinc-500 font-mono">NTA. 02.21.0002</span>
                </div>
            </div>

            <div class="space-y-16">
                <p>Mengesahkan,<br><strong>Kwartir Cabang Kota Padangsidimpuan</strong><br>Ketua Harian</p>
                <div>
                    <strong class="border-b border-zinc-800 pb-0.5 block font-bold uppercase">( Pimpinan Kwarcab )</strong>
                    <span class="text-[10px] text-zinc-500 font-mono">NTA. 02.21.0000</span>
                </div>
            </div>
        </div>

        {{-- Catatan Kaki Otomatis Sistem --}}
        <div class="pt-4 border-t border-zinc-200 flex items-center justify-between text-[10px] text-zinc-400 font-mono">
            <span>Dicetak secara otomatis melalui Sistem Informasi LT-III Kwarcab Padangsidimpuan</span>
            <span>{{ $generatedAt->format('d/m/Y H:i:s') }} WIB</span>
        </div>
    </div>

</body>
</html>
