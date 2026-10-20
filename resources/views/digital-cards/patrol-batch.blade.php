@php
    $theme = $patrol->isBoys() ? 'boys' : 'girls';
    $chunks = $participantsWithQr->chunk(4);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CETAK KOLEKTIF ID CARD REGU (FORMAT B2) - {{ $patrol->patrol_name }} ({{ $scoutGroup->school_name }})</title>
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
            <strong class="text-[13px] font-bold text-slate-100 block">ID Card Format B2 (A4 Batch)</strong>
            <div class="text-[11px] text-amber-400 mt-0.5">Regu {{ $patrol->patrol_name }} &bull; {{ $scoutGroup->school_name }} &bull; Total {{ $participantsWithQr->count() }} Anggota</div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="javascript:window.history.back()" class="inline-flex items-center gap-1.5 px-3 py-2 text-[13px] font-semibold rounded-lg bg-slate-700 border border-slate-600 text-slate-100 hover:bg-slate-600 transition">&larr; Kembali</a>
            <button type="button"
                onclick="window.saveCumulativeCardsAsZip ? window.saveCumulativeCardsAsZip('[data-digital-card]', 'KARTU_REGU_{{ Str::slug($patrol->patrol_name) }}') : alert('Menyiapkan kompresor ZIP...')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition cursor-pointer shadow-sm">
                💾 Simpan Semua .PNG (ZIP)
            </button>
            <button type="button"
                onclick="window.saveSheetAsPng ? window.saveSheetAsPng(0, 'LEMBAR_A4_REGU_{{ Str::slug($patrol->patrol_name) }}') : alert('Menyiapkan lembar PNG...')"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-[13px] font-semibold rounded-lg bg-teal-600 hover:bg-teal-500 text-white transition cursor-pointer shadow-sm">
                🖼 Simpan Lembar .PNG
            </button>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-amber-600 hover:bg-amber-500 text-white transition cursor-pointer">&#128438; Cetak A4 (Ctrl+P)</button>
        </div>
    </div>

    @forelse($chunks as $pageIndex => $pageParticipants)
        <div data-sheet-page class="w-[210mm] min-h-[297mm] mx-auto mb-5 p-[7mm_12mm] bg-white shadow-md print:shadow-none print:m-0 print:p-[7mm_12mm] break-after-page last:break-after-auto relative">
            <div class="text-center border-b-2 border-amber-600 pb-1 mb-[5mm]">
                <h2 class="text-[10.5pt] font-extrabold uppercase text-amber-700 m-0 leading-tight">LEMBAR IDENTITAS PESERTA LT III (FORMAT B2) &bull; HALAMAN {{ $pageIndex + 1 }} DARI {{ $chunks->count() }}</h2>
                <p class="text-[7.5pt] text-slate-500 mt-0.5 leading-tight">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan &bull; Pangkalan {{ $scoutGroup->school_name }} (Gudep {{ $patrol->group_number }})</p>
            </div>

            <div class="grid grid-cols-2 gap-x-[6mm] gap-y-[4mm] justify-items-center">
                @foreach($pageParticipants as $participant)
                    @php
                        $role = $participant->isLeader() ? 'leader' : ($participant->isAssistantLeader() ? 'assistant_leader' : 'member');
                        $photoUrl = $participant->photo_path ? asset('storage/' . $participant->photo_path) : null;
                    @endphp
                    <div class="w-[79mm] h-[126mm]">
                        <x-digital-card.card data-card-name="{{ $participant->full_name }}" data-card-id="{{ $participant->id_number }}">
                            <x-digital-card.header :theme="$theme" />
                            <x-digital-card.category-strip :theme="$theme">
                                {{ $patrol->isBoys() ? 'PESERTA PENGGALANG PUTRA' : 'PESERTA PENGGALANG PUTRI' }}
                            </x-digital-card.category-strip>
                            <x-digital-card.body>
                                @if($patrol->patrol_name)
                                    <x-digital-card.patrol-badge :name="$patrol->patrol_name" :theme="$theme" />
                                @endif
                                <x-digital-card.photo :photo-url="$photoUrl" :name="$participant->full_name" :role="$role" />
                                <x-digital-card.meta-info :name="$participant->full_name" :scout-id="$participant->scout_id" :id-number="$participant->id_number ?: 'ID-PESERTA'" :theme="$theme" />
                                <x-digital-card.details-table>
                                    <x-digital-card.details-row label="Nomor Gudep" :value="$patrol->group_number" />
                                    <x-digital-card.details-row label="Pangkalan" :value="Str::limit($scoutGroup->school_name, 22)" />
                                    <x-digital-card.details-row label="Kwartir Ranting" :value="$scoutGroup->kwarran_name ?? 'Padangsidimpuan'" />
                                    <x-digital-card.details-row label="Kontak/WA" :value="$participant->phone ?: ($participant->parent_phone ?: '-')" />
                                </x-digital-card.details-table>
                                <x-digital-card.qr-section :qr-data-uri="$participant->qrDataUri" :token="$participant->qr_token" :theme="$theme" />
                            </x-digital-card.body>
                            <x-digital-card.footer :theme="$theme">
                                PANITIA LT III 2026 &bull; KWARCAB PADANGSIDIMPUAN
                            </x-digital-card.footer>
                        </x-digital-card.card>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="w-[210mm] mx-auto bg-white p-8 rounded-xl text-center text-slate-500">
            Belum ada anggota terdaftar dalam regu ini.
        </div>
    @endforelse

</body>
</html>
