@php
    // Kumpulkan seluruh kartu peserta dari semua regu dan bindamping dalam kontingen ini
    $allCards = collect();

    foreach ($scoutGroup->patrols as $patrol) {
        $patrolTheme = $patrol->isBoys() ? 'boys' : 'girls';
        foreach ($patrol->participants as $participant) {
            $allCards->push([
                'type' => 'participant',
                'patrol' => $patrol,
                'participant' => $participant,
                'theme' => $patrolTheme,
                'name' => $participant->full_name,
                'id_number' => $participant->id_number ?: 'ID-PESERTA',
                'scout_id' => $participant->scout_id,
                'photo_path' => $participant->photo_path,
                'qr_token' => $participant->qr_token,
                'qrDataUri' => $participant->qrDataUri,
                'role' => $participant->isLeader() ? 'leader' : ($participant->isAssistantLeader() ? 'assistant_leader' : 'member'),
                'category_label' => $patrol->isBoys() ? 'PESERTA PENGGALANG PUTRA' : 'PESERTA PENGGALANG PUTRI',
                'group_number' => $patrol->group_number,
                'contact' => $participant->phone ?: ($participant->parent_phone ?: '-'),
            ]);
        }
    }

    foreach ($scoutGroup->chaperones as $chaperone) {
        $chaperoneTheme = $chaperone->gender === 'male' ? 'chaperone-male' : 'chaperone-female';
        $allCards->push([
            'type' => 'chaperone',
            'chaperone' => $chaperone,
            'theme' => $chaperoneTheme,
            'name' => $chaperone->full_name,
            'id_number' => $chaperone->id_number ?: 'ID-BINDAMPING',
            'scout_id' => $chaperone->scout_id,
            'photo_path' => $chaperone->photo_path,
            'qr_token' => $chaperone->qr_token,
            'qrDataUri' => $chaperone->qrDataUri,
            'role' => null,
            'category_label' => 'BINDAMPING ' . ($chaperone->gender === 'male' ? 'PUTRA' : 'PUTRI'),
            'group_number' => $chaperone->group_number ?: ($scoutGroup->group_number ?? '-'),
            'contact' => $chaperone->phone_number ?: '-',
        ]);
    }

    $chunks = $allCards->chunk(4);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CETAK KOLEKTIF ID CARD KONTINGEN (FORMAT B2) - {{ $scoutGroup->school_name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('resources/images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A4 portrait;
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
<body class="bg-slate-900 py-6 print:bg-transparent print:p-0">

    <div class="max-w-[210mm] mx-auto mb-4 flex flex-wrap justify-between items-center gap-3 bg-slate-800 p-3.5 rounded-xl border border-slate-700 text-slate-50 shadow-md print:hidden">
        <div>
            <strong class="text-[13px] font-bold text-slate-100 block">ID Card Kontingen Format B2 (A4 Batch)</strong>
            <div class="text-[11px] text-amber-400 mt-0.5">{{ $scoutGroup->school_name }} &bull; Total {{ $allCards->count() }} Kartu ({{ $scoutGroup->patrols->count() }} Regu, {{ $scoutGroup->chaperones->count() }} Pembina)</div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="javascript:window.history.back()" class="inline-flex items-center gap-1.5 px-3 py-2 text-[13px] font-semibold rounded-lg bg-slate-700 border border-slate-600 text-slate-100 hover:bg-slate-600 transition">&larr; Kembali</a>
            <button type="button"
                onclick="window.saveCumulativeCardsAsZip ? window.saveCumulativeCardsAsZip('[data-digital-card]', 'KARTU_KONTINGEN_{{ Str::slug($scoutGroup->school_name) }}') : alert('Menyiapkan kompresor ZIP...')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition cursor-pointer shadow-sm">
                💾 Simpan Semua .PNG (ZIP)
            </button>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-amber-600 hover:bg-amber-500 text-white transition cursor-pointer">&#128438; Cetak A4 (Ctrl+P)</button>
        </div>
    </div>

    @forelse($chunks as $pageIndex => $pageCards)
        <div data-sheet-page class="w-[210mm] min-h-[297mm] mx-auto mb-5 p-[7mm_12mm] bg-white shadow-md print:shadow-none print:m-0 print:p-[7mm_12mm] break-after-page last:break-after-auto relative">
            <div class="text-center border-b-2 border-amber-600 pb-1 mb-[5mm]">
                <h2 class="text-[10.5pt] font-extrabold uppercase text-amber-700 m-0 leading-tight">LEMBAR IDENTITAS KONTINGEN LT III (FORMAT B2) &bull; HALAMAN {{ $pageIndex + 1 }} DARI {{ $chunks->count() }}</h2>
                <p class="text-[7.5pt] text-slate-500 mt-0.5 leading-tight">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan &bull; Pangkalan {{ $scoutGroup->school_name }} (Gudep {{ $scoutGroup->group_number }})</p>
            </div>

            <div class="grid grid-cols-2 gap-x-[6mm] gap-y-[4mm] justify-items-center">
                @foreach($pageCards as $item)
                    @php
                        $photoUrl = $item['photo_path'] ? asset('storage/' . $item['photo_path']) : null;
                    @endphp
                    <div class="w-[79mm] h-[126mm]">
                        <x-digital-card.card data-card-name="{{ $item['name'] }}" data-card-id="{{ $item['id_number'] }}">
                            <x-digital-card.header :theme="$item['theme']" />
                            <x-digital-card.category-strip :theme="$item['theme']">
                                {{ $item['category_label'] }}
                            </x-digital-card.category-strip>
                            <x-digital-card.body>
                                @if(isset($item['patrol']) && $item['patrol']->patrol_name)
                                    <x-digital-card.patrol-badge :name="$item['patrol']->patrol_name" :theme="$item['theme']" />
                                @endif
                                <x-digital-card.photo :photo-url="$photoUrl" :name="$item['name']" :role="$item['role']" />
                                <x-digital-card.meta-info :name="$item['name']" :scout-id="$item['scout_id']" :id-number="$item['id_number']" :theme="$item['theme']" />
                                <x-digital-card.details-table>
                                    <x-digital-card.details-row label="Nomor Gudep" :value="$item['group_number']" />
                                    <x-digital-card.details-row label="Pangkalan" :value="Str::limit($scoutGroup->school_name, 22)" />
                                    <x-digital-card.details-row label="Kwartir Ranting" :value="$scoutGroup->kwarran_name ?? 'Padangsidimpuan'" />
                                    <x-digital-card.details-row label="Kontak/WA" :value="$item['contact']" />
                                </x-digital-card.details-table>
                                <x-digital-card.qr-section :qr-data-uri="$item['qrDataUri']" :token="$item['qr_token']" :theme="$item['theme']" />
                            </x-digital-card.body>
                            <x-digital-card.footer :theme="$item['theme']">
                                PANITIA LT III 2026 &bull; KWARCAB PADANGSIDIMPUAN
                            </x-digital-card.footer>
                        </x-digital-card.card>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="w-[210mm] mx-auto bg-white p-8 rounded-xl text-center text-slate-500">
            Belum ada kartu anggota atau pembina yang terdaftar dalam kontingen ini.
        </div>
    @endforelse

</body>
</html>
