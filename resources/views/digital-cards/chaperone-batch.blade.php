@php
    $chunks = $chaperonesWithQr->chunk(4);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CETAK KOLEKTIF ID CARD BINDAMPING (FORMAT B2) - {{ $scoutGroup->school_name }}</title>
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
<body class="bg-[#064e3b] py-6 print:bg-transparent print:p-0">

    <div class="max-w-[210mm] mx-auto mb-4 flex flex-wrap justify-between items-center gap-3 bg-[#062f24] p-3.5 rounded-xl border border-[#047857] text-slate-50 shadow-md print:hidden">
        <div>
            <strong class="text-[13px] font-bold text-slate-100 block">ID Card Bindamping Format B2 (A4 Batch)</strong>
            <div class="text-[11px] text-emerald-300 mt-0.5">{{ $scoutGroup->school_name }} &bull; Total {{ $chaperonesWithQr->count() }} Pembina Pendamping</div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="javascript:window.history.back()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-[#064e3b] border border-[#047857] text-slate-100 hover:bg-[#047857] transition">&larr; Kembali</a>
            <button type="button"
                onclick="window.saveCumulativeCardsAsZip ? window.saveCumulativeCardsAsZip('[data-digital-card]', 'KARTU_BINDAMPING_{{ Str::slug($scoutGroup->school_name) }}') : alert('Menyiapkan kompresor ZIP...')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition cursor-pointer shadow-sm">
                💾 Simpan Semua .PNG (ZIP)
            </button>
            <button type="button"
                onclick="window.saveSheetAsPng ? window.saveSheetAsPng(0, 'LEMBAR_A4_BINDAMPING_{{ Str::slug($scoutGroup->school_name) }}') : alert('Menyiapkan lembar PNG...')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-teal-600 hover:bg-teal-500 text-white transition cursor-pointer shadow-sm">
                🖼 Simpan Lembar .PNG
            </button>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition cursor-pointer">&#128438; Cetak A4 (Ctrl+P)</button>
        </div>
    </div>

    @forelse($chunks as $pageIndex => $pageChaperones)
        <div data-sheet-page class="w-[210mm] min-h-[297mm] mx-auto mb-5 p-[7mm_12mm] bg-white shadow-md print:shadow-none print:m-0 print:p-[7mm_12mm] break-after-page last:break-after-auto relative">
            <div class="text-center border-b-2 border-emerald-700 pb-1 mb-[5mm]">
                <h2 class="text-[10.5pt] font-extrabold uppercase text-emerald-900 m-0 leading-tight">LEMBAR IDENTITAS KARTU PEMBINA PENDAMPING LT III (FORMAT B2) &bull; HALAMAN {{ $pageIndex + 1 }} DARI {{ $chunks->count() }}</h2>
                <p class="text-[7.5pt] text-slate-500 mt-0.5 leading-tight">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan &bull; Pangkalan {{ $scoutGroup->school_name }} (Gudep {{ $scoutGroup->group_number }})</p>
            </div>

            <div class="grid grid-cols-2 gap-x-[6mm] gap-y-[4mm] justify-items-center">
                @foreach($pageChaperones as $chaperone)
                    @php
                        $theme = $chaperone->gender === 'male' ? 'chaperone-male' : 'chaperone-female';
                        $photoUrl = $chaperone->photo_path ? asset('storage/' . $chaperone->photo_path) : null;
                    @endphp
                    <div class="w-[79mm] h-[126mm]">
                        <x-digital-card.card data-card-name="{{ $chaperone->full_name }}" data-card-id="{{ $chaperone->id_number }}">
                            <x-digital-card.header :theme="$theme" />
                            <x-digital-card.category-strip :theme="$theme">
                                BINDAMPING {{ $chaperone->gender === 'male' ? 'PUTRA' : 'PUTRI' }}
                            </x-digital-card.category-strip>
                            <x-digital-card.body>
                                <x-digital-card.photo :photo-url="$photoUrl" :name="$chaperone->full_name" />
                                <x-digital-card.meta-info :name="$chaperone->full_name" :scout-id="$chaperone->scout_id" :id-number="$chaperone->id_number ?: 'ID-BINDAMPING'" :theme="$theme" />
                                <x-digital-card.details-table>
                                    <x-digital-card.details-row label="Pangkalan" :value="Str::limit($scoutGroup->school_name, 22)" />
                                    <x-digital-card.details-row label="Nomor Gudep" :value="$chaperone->group_number ?: ($scoutGroup->group_number ?? '-')" />
                                    <x-digital-card.details-row label="Kwartir Ranting" :value="$scoutGroup->kwarran_name ?? 'Padangsidimpuan'" />
                                    <x-digital-card.details-row label="Kontak" :value="$chaperone->phone_number ?: '-'" />
                                </x-digital-card.details-table>
                                <x-digital-card.qr-section :qr-data-uri="$chaperone->qrDataUri" :token="$chaperone->qr_token" :theme="$theme" />
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
            Belum ada pembina pendamping terdaftar.
        </div>
    @endforelse

</body>
</html>
