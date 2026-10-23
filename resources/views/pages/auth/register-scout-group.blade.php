<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Registrasi Akun Pangkalan Gugus Depan &mdash; LT III Kwarcab Padangsidimpuan</title>
    <meta name="description" content="Pendaftaran akun resmi pangkalan Gugus Depan (Sekolah) peserta Lomba Tingkat III (LT III) Kwarcab Gerakan Pramuka Kota Padangsidimpuan.">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    {{-- Styles & Scripts via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased selection:bg-amber-500 selection:text-white dark:bg-zinc-950 dark:text-zinc-100 flex flex-col font-sans">
    @php
        $event = $event ?? \App\Models\Event::where('is_active', true)->first();
        $isRegistrationOpen = $isRegistrationOpen ?? ($event ? $event->isRegistrationOpen() : config('registration.is_enabled', true));
        $kwarrans = $kwarrans ?? [
            'Kwarran Padangsidimpuan Utara',
            'Kwarran Padangsidimpuan Selatan',
            'Kwarran Padangsidimpuan Batunadua',
            'Kwarran Padangsidimpuan Hutaimbaru',
            'Kwarran Padangsidimpuan Tenggara',
            'Kwarran Padangsidimpuan Angkola Julu',
        ];
    @endphp

    {{-- Dedicated Top Navigation Bar for Registration Template --}}
    <x-public-header />

    {{-- Main Container --}}
    <main class="flex-1 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Header Badge & Title --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200/80 pb-6 dark:border-zinc-800">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-300 mb-2">
                        <span class="size-1.5 rounded-full bg-amber-600"></span>
                        Formulir Registrasi Mandiri Pangkalan
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                        Registrasi Akun Pangkalan Gugus Depan
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 max-w-2xl">
                        Daftarkan akun resmi pangkalan sekolah untuk memperoleh akses ke Dasbor Kontingen LT III Kwarcab Padangsidimpuan.
                    </p>
                </div>

                {{-- Status Batas Waktu --}}
                @if ($event && $event->registration_end_at)
                    <div class="rounded-2xl border {{ $isRegistrationOpen ? 'border-amber-200 bg-amber-50/70 dark:border-amber-900/40 dark:bg-amber-950/30' : 'border-rose-200 bg-rose-50/70 dark:border-rose-900/40 dark:bg-rose-950/30' }} p-3.5 shadow-xs flex items-center gap-3 self-start sm:self-auto">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $isRegistrationOpen ? 'bg-amber-600 text-white' : 'bg-rose-600 text-white' }} font-bold shadow-xs">
                            <flux:icon name="calendar" class="size-4.5" />
                        </div>
                        <div class="text-xs">
                            <span class="block font-semibold uppercase tracking-wider text-[10px] {{ $isRegistrationOpen ? 'text-amber-800 dark:text-amber-300' : 'text-rose-800 dark:text-rose-300' }}">
                                Batas Waktu Registrasi Pangkalan
                            </span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $event->registration_end_at->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Grid: Left Information & Right Form --}}
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
                {{-- Left Sidebar: Guidelines & Information --}}
                <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-24">
                    {{-- Card Alur Sistem Pendaftaran --}}
                    <div class="rounded-2xl border border-amber-300/70 bg-gradient-to-br from-amber-50/90 to-white p-5 shadow-sm dark:border-amber-500/20 dark:from-zinc-900 dark:to-zinc-900/90">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white font-bold shadow-md shadow-amber-600/20">
                                <flux:icon name="identification" class="size-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Alur Pendaftaran Kontingen</h3>
                                <span class="text-[11px] text-amber-700 dark:text-amber-400 font-medium">LT III Kwarcab Padangsidimpuan</span>
                            </div>
                        </div>

                        <ol class="mt-5 space-y-3.5 text-xs">
                            <li class="flex items-start gap-3">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-[11px] font-bold text-white shadow-xs">1</span>
                                <div>
                                    <strong class="block font-semibold text-zinc-900 dark:text-zinc-100">Registrasi Akun Gudep</strong>
                                    <span class="text-zinc-500">Mendaftarkan identitas sekolah & akun PIC penanggung jawab (formulir ini).</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">2</span>
                                <div>
                                    <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Input Regu & Peserta di Dasbor</strong>
                                    <span class="text-zinc-500">Bentuk Regu Putra/Putri dan entri biodata peserta di dalam <em>Dasbor Gudep</em>.</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">3</span>
                                <div>
                                    <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Input Bindamping di Dasbor</strong>
                                    <span class="text-zinc-500">Entri data Pembina Pendamping putra/putri di dalam <em>Dasbor Gudep</em>.</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-[11px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">4</span>
                                <div>
                                    <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Verifikasi Berkas oleh Administrator</strong>
                                    <span class="text-zinc-500">Pemeriksaan & persetujuan kelengkapan kontingen oleh Administrator serta penerbitan kartu digital QR Code.</span>
                                </div>
                            </li>
                        </ol>

                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-100/50 p-3 text-xs text-amber-950 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-200">
                            <strong class="font-bold flex items-center gap-1.5 mb-1">
                                <flux:icon name="information-circle" class="size-4 text-amber-700 dark:text-amber-400" />
                                Verifikasi Administrator:
                            </strong>
                            <p class="text-[11px] leading-relaxed">
                                Pendaftaran akun pangkalan <strong>tidak memerlukan email</strong>. Akun langsung aktif dan dapat digunakan. Seluruh data kontingen (regu, peserta, bindamping) selanjutnya <strong>diverifikasi langsung oleh Administrator</strong>.
                            </p>
                        </div>
                    </div>

                    @php
                        $paymentConfig = \App\Models\Event::activePaymentConfig();
                    @endphp

                    {{-- Card Ketentuan Biaya Pendaftaran per Regu --}}
                    <div class="rounded-2xl border border-amber-300/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Biaya Pendaftaran Kontingen</h4>
                            <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">Per Regu</span>
                        </div>
                        <div class="my-3">
                            <span class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">{{ $paymentConfig['formatted_fee_per_patrol'] }}</span>
                            <span class="text-xs text-zinc-500">/ regu</span>
                            <p class="mt-1 text-[11px] text-zinc-500 leading-normal">
                                Dihitung berdasarkan jumlah regu (Putra/Putri) yang didaftarkan. Pembayaran ditransfer ke rekening resmi Kwarcab saat pengajuan berkas di dasbor.
                            </p>
                        </div>

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-800/40 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block">Rekening Resmi Kwarcab:</span>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100 text-xs">{{ $paymentConfig['bank_name'] }}</div>
                            <div class="font-mono font-bold text-amber-800 dark:text-amber-400 text-xs tracking-wide select-all">{{ $paymentConfig['account_number'] }}</div>
                            <div class="text-[10px] text-zinc-600 dark:text-zinc-400 leading-tight">{{ $paymentConfig['account_holder'] }}</div>
                        </div>
                    </div>

                    {{-- Card Ketentuan Pangkalan --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Ketentuan Pangkalan</h4>
                        <ul class="mt-3 space-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                            <li class="flex items-start gap-2">
                                <flux:icon name="check" class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                                <span>Pangkalan resmi SMP/MTs sederajat di 6 Kwartir Ranting Kota Padangsidimpuan.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <flux:icon name="check" class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                                <span>Setiap sekolah hanya mendaftarkan <strong>1 akun resmi</strong> penanggung jawab.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <flux:icon name="check" class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                                <span>Nomor WhatsApp aktif digunakan untuk konfirmasi verifikasi dan pemberitahuan pos lomba.</span>
                            </li>
                        </ul>

                        <div class="mt-4 border-t border-zinc-100 pt-3 text-xs dark:border-zinc-800">
                            <span class="text-zinc-500">Sudah memiliki akun pangkalan?</span>
                            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-amber-700 hover:underline dark:text-amber-400">
                                Masuk ke Dasbor &rarr;
                            </a>
                        </div>

                        <div class="mt-3">
                            <flux:button href="{{ route('guide') }}" wire:navigate icon="book-open-text" variant="subtle" size="sm" class="w-full">
                            Baca Panduan Pendaftaran &amp; Penggunaan
                        </flux:button>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Registration Form or Closed Notice --}}
                <div class="lg:col-span-8">
                    @if (!$isRegistrationOpen)
                        {{-- Notice: Pendaftaran Ditutup --}}
                        <div class="rounded-2xl border border-rose-200 bg-white p-8 text-center shadow-sm dark:border-rose-900/50 dark:bg-zinc-900">
                            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                                <flux:icon name="lock-closed" class="size-7" />
                            </div>
                            <h2 class="mt-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
                                Pendaftaran Akun Gugus Depan Telah Ditutup
                            </h2>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 max-w-lg mx-auto leading-relaxed">
                                Batas waktu pendaftaran akun pangkalan untuk Lomba Tingkat III Kwarcab Padangsidimpuan telah berakhir pada {{ $event?->registration_end_at?->translatedFormat('d F Y, H:i') ?? 'jadwal resmi' }} WIB.
                            </p>
                            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                                <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-600/25">
                                    Masuk ke Dasbor Kontingen
                                </a>
                                <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                    Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Registration Form --}}
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                            @if ($errors->any())
                                <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 p-4 text-xs text-rose-800 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">
                                    <strong class="font-bold flex items-center gap-1.5 text-sm mb-1">
                                        <flux:icon name="exclamation-circle" class="size-4 text-rose-600" />
                                        Mohon periksa kembali isian formulir pendaftaran:
                                    </strong>
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register.scout-group.store') }}" class="space-y-6">
                                @csrf

                                {{-- Section 1: Data Pangkalan / Sekolah --}}
                                <div>
                                    <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                                        <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">A</span>
                                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                            Identitas Pangkalan Gugus Depan (Sekolah)
                                        </h3>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        {{-- Nama Sekolah / Pangkalan --}}
                                        <div class="sm:col-span-2">
                                            <flux:input name="school_name" :label="__('Nama Pangkalan / Sekolah')"
                                                :value="old('school_name')" type="text" required autofocus
                                                placeholder="Contoh: SMP Negeri 2 Padangsidimpuan" />
                                            <p class="mt-1 text-[11px] text-zinc-500">Tuliskan nama resmi sekolah tingkat SMP/MTs sederajat.</p>
                                        </div>

                                        {{-- Kwartir Ranting (Kwarran) --}}
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Kwartir Ranting (Kwarran) <span class="text-rose-500">*</span>
                                            </label>
                                            <select name="kwarran" required
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">-- Pilih Kwartir Ranting (Kwarran) --</option>
                                                @foreach ($kwarrans as $ranting)
                                                    <option value="{{ $ranting }}" @selected(old('kwarran') === $ranting)>
                                                        {{ $ranting }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Alamat Lengkap Pangkalan --}}
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Alamat Lengkap Pangkalan <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" name="address" value="{{ old('address') }}" required
                                                placeholder="Contoh: Jl. Sisingamangaraja No. 45, Kel. Sitamiang"
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>

                                        {{-- Nomor Gugus Depan (Otomatisasi Ganjil Putra & Genap Putri) --}}
                                        <div class="sm:col-span-2 rounded-xl border border-zinc-200/90 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-900/40"
                                            x-data="{
                                                rawInput: '{{ old('group_number', '') }}',
                                                init() {
                                                    if (this.rawInput) {
                                                        this.rawInput = this.formatGroupNumber(this.rawInput);
                                                    }
                                                },
                                                formatGroupNumber(val) {
                                                    if (!val) return '';
                                                    let cleaned = val.replace(/[^\d-]/g, '');
                                                    if (cleaned.includes('-')) {
                                                        let parts = cleaned.split('-');
                                                        let prefix = parts[0].slice(0, 2);
                                                        let num = parts.slice(1).join('').replace(/\D/g, '').slice(0, 3);
                                                        return num.length > 0 ? (prefix + '-' + num) : (prefix + '-');
                                                    }
                                                    let digits = cleaned.replace(/\D/g, '');
                                                    if (digits.length > 2) {
                                                        return digits.slice(0, 2) + '-' + digits.slice(2, 5);
                                                    }
                                                    return digits.slice(0, 2);
                                                },
                                                onInput(e) {
                                                    let formatted = this.formatGroupNumber(e.target.value);
                                                    this.rawInput = formatted;
                                                    e.target.value = formatted;
                                                },
                                                handleBlur() {
                                                    let clean = this.rawInput.trim();
                                                    if (/^\d{1,3}$/.test(clean)) {
                                                        let n = parseInt(clean, 10);
                                                        let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                                        this.rawInput = '01-' + String(odd).padStart(3, '0');
                                                    } else if (/^(\d{2})[-.]?(\d{1,3})$/.test(clean)) {
                                                        let m = clean.match(/^(\d{2})[-.]?(\d{1,3})$/);
                                                        let p = m[1];
                                                        let n = parseInt(m[2], 10);
                                                        let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                                        this.rawInput = p + '-' + String(odd).padStart(3, '0');
                                                    }
                                                },
                                                get maleNum() {
                                                    let clean = this.rawInput.trim();
                                                    let m = clean.match(/^(\d{2})[-.]?(\d+)$/);
                                                    if (m) {
                                                        let p = m[1];
                                                        let n = parseInt(m[2], 10);
                                                        let len = Math.max(3, m[2].length);
                                                        let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                                        return p + '-' + String(odd).padStart(len, '0');
                                                    }
                                                    let digits = clean.replace(/\D/g, '');
                                                    if (!digits) return '';
                                                    let n = parseInt(digits, 10);
                                                    let len = Math.max(3, digits.length);
                                                    let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                                    return '01-' + String(odd).padStart(len, '0');
                                                },
                                                get femaleNum() {
                                                    if (!this.maleNum) return '';
                                                    let parts = this.maleNum.split('-');
                                                    let p = parts[0];
                                                    let n = parseInt(parts[1], 10);
                                                    let len = parts[1].length;
                                                    return p + '-' + String(n + 1).padStart(len, '0');
                                                },
                                                get combined() {
                                                    if (!this.maleNum || !this.femaleNum) return '';
                                                    return this.maleNum + ' & ' + this.femaleNum;
                                                }
                                            }">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                                        Nomor Gudep Putra<span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="text" name="group_number" x-model="rawInput"
                                                        @input="onInput($event)" @blur="handleBlur()" required
                                                        placeholder="Contoh: 01-093"
                                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-sm text-zinc-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                                    <p class="mt-1 text-[11px] text-zinc-500">
                                                        Cukup ketik nomor depan ganjil (misal: <strong>01-093</strong>). Nomor genap putri akan otomatis terisi.
                                                    </p>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                                        Nomor Gudep Putri (Otomatis)
                                                    </label>
                                                    <input type="text" :value="femaleNum || 'Otomatis dihitung...'"
                                                        readonly
                                                        class="w-full rounded-lg border border-zinc-200 bg-zinc-100/80 px-3.5 py-2 text-sm font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-400 cursor-not-allowed" />
                                                    <p class="mt-1 text-[11px] text-zinc-500">
                                                        Otomatis dihitung (nomor ganjil + 1) dan melekat pada regu putri.
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Live Preview Tag --}}
                                            <div x-show="maleNum" x-cloak
                                                class="mt-3.5 flex flex-wrap items-center gap-2 border-t border-zinc-200/80 pt-3 dark:border-zinc-800">
                                                <span class="text-[11px] font-medium text-zinc-500">Hasil Format:</span>
                                                <span class="inline-flex items-center gap-1 rounded-md bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                                    <span class="size-1.5 rounded-full bg-blue-600"></span>
                                                    Putra: <span x-text="maleNum"></span>
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                    <span class="size-1.5 rounded-full bg-rose-600"></span>
                                                    Putri: <span x-text="femaleNum"></span>
                                                </span>
                                                <span class="text-[11px] font-semibold text-amber-800 dark:text-amber-300">
                                                    Format Resmi: <span x-text="combined"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Section 2: Data Akun Penanggung Jawab Kontingen (PIC) --}}
                                <div class="pt-2">
                                    <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                                        <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">B</span>
                                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                            Akun Pembina Penanggung Jawab (PIC Kontingen)
                                        </h3>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        {{-- Nama Lengkap PIC --}}
                                        <div>
                                            <flux:input name="pic_name" :label="__('Nama Lengkap Penanggung Jawab (PIC)')"
                                                :value="old('pic_name')" type="text" required
                                                placeholder="Contoh: Marwan Daulay, S.Pd" />
                                            <p class="mt-1 text-[11px] text-zinc-500">Nama pembina / guru penanggung jawab pangkalan.</p>
                                        </div>

                                        {{-- Nomor WhatsApp / HP --}}
                                        <div>
                                            <flux:input name="phone_number" :label="__('Nomor WhatsApp / HP Aktif')"
                                                :value="old('phone_number')" type="text" required
                                                placeholder="Contoh: 081234567890" />
                                            <p class="mt-1 text-[11px] text-zinc-500">Digunakan Administrator untuk konfirmasi berkas verifikasi kontingen.</p>
                                        </div>

                                        {{-- Informasi Otomatisasi Kredensial Akun --}}
                                        <div class="sm:col-span-2 rounded-xl border border-amber-300/80 bg-gradient-to-br from-amber-50/90 to-amber-100/50 p-4 dark:border-amber-500/30 dark:from-amber-950/40 dark:to-zinc-900/60 shadow-xs">
                                            <div class="flex items-start gap-3">
                                                <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white shadow-xs">
                                                    <flux:icon name="sparkles" class="size-4" />
                                                </div>
                                                <div class="text-xs">
                                                    <strong class="block font-bold text-amber-950 dark:text-amber-200">
                                                        Kredensial Akun Dibuat Otomatis oleh Sistem
                                                    </strong>
                                                    <p class="mt-0.5 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                                        Anda tidak perlu membuat username dan kata sandi secara manual. Sistem akan secara otomatis menerbitkan <strong>ID Gudep resmi unik</strong>, <strong>username</strong>, dan <strong>kata sandi aman</strong> segera setelah pendaftaran dikirim. Anda akan langsung diarahkan untuk melihat kredensial tersebut serta dapat langsung mengubah kata sandi.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Section 3: Pakta Pernyataan & Persetujuan --}}
                                <div class="rounded-xl border border-amber-200/80 bg-amber-50/50 p-4 dark:border-amber-500/20 dark:bg-amber-950/30">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="terms" value="1" required
                                            @checked(old('terms'))
                                            class="size-4 mt-0.5 rounded border-amber-300 text-amber-600 focus:ring-amber-500 dark:border-amber-700 dark:bg-zinc-900" />
                                        <span class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                            Saya menyatakan bahwa data identitas pangkalan dan penanggung jawab di atas
                                            adalah benar dan sah mewakili Gugus Depan pangkalan sekolah, serta bersedia
                                            mematuhi seluruh Petunjuk Pelaksanaan dan Petunjuk Teknis Lomba Tingkat III
                                            Kwarcab Padangsidimpuan.
                                        </span>
                                    </label>
                                </div>

                                {{-- Section 4: Tombol Aksi --}}
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                                    <a href="{{ route('home') }}" wire:navigate
                                        class="text-xs font-medium text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200">
                                        &larr; Batal & Kembali ke Beranda
                                    </a>

                                    <flux:button type="submit" variant="primary"
                                        class="w-full sm:w-auto bg-gradient-to-r from-amber-600 to-amber-700 hover:brightness-105 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md shadow-amber-600/25"
                                        data-test="register-scout-group-button">
                                        Daftarkan Pangkalan & Masuk Dasbor &rarr;
                                    </flux:button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    {{-- Dedicated Footer --}}
    <footer class="mt-auto">
        <x-official-footer />
    </footer>

    @fluxScripts
</body>
</html>
