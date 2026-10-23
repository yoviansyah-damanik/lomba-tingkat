<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Batas Waktu Registrasi Kontingen Gugus Depan (LT III)
    |--------------------------------------------------------------------------
    |
    | Menentukan batas akhir pembukaan pendaftaran akun pangkalan Gugus Depan
    | serta pengisian data regu, peserta, dan pembina pendamping di dasbor.
    |
    */

    'deadline' => env('SCOUT_GROUP_REGISTRATION_DEADLINE', '2026-11-10 23:59:59'),

    'is_enabled' => env('SCOUT_GROUP_REGISTRATION_ENABLED', true),

    'timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Biaya Pendaftaran Kontingen per Regu & Rekening Kwarcab
    |--------------------------------------------------------------------------
    |
    | Biaya pendaftaran dihitung per regu yang didaftarkan oleh pangkalan.
    | Nilai ini dapat ditimpa melalui kolom di tabel master events.
    |
    */

    'fee_per_patrol' => (int) env('REGISTRATION_FEE_PER_PATROL', 250000),

    /*
    | Tampilkan pengaturan 2FA dan Passkeys pada halaman keamanan akun.
    | Fitur Fortify tetap aktif; hanya antarmuka pengaturannya yang disembunyikan.
    */
    'show_2fa_passkeys' => (bool) env('SHOW_2FA_PASSKEYS', false),

    'payment_bank' => env('REGISTRATION_PAYMENT_BANK', 'Bank Sumut'),

    'payment_account_number' => env('REGISTRATION_PAYMENT_ACCOUNT_NUMBER', '101-02-04-001234-5'),

    'payment_account_holder' => env('REGISTRATION_PAYMENT_ACCOUNT_HOLDER', 'Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan'),

    'payment_instructions' => env('REGISTRATION_PAYMENT_INSTRUCTIONS', 'Pembayaran biaya pendaftaran kontingen dihitung per regu yang didaftarkan. Harap mencantumkan nama pangkalan pada struk/berita transfer (contoh: SMPN 1 Padangsidimpuan).'),
];
