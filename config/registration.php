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
];
