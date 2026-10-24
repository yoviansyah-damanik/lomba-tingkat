<?php

/**
 * Data resmi LT-III Tahun 2026 Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan
 * (bersumber dari Petunjuk Pelaksanaan No. 12 Tahun 2026 dan Petunjuk Teknis No. 14 Tahun 2026).
 *
 * Catatan: penomoran kegiatan mengikuti daftar Bab III.A Juknis (Lomba nomor 8 s.d. 24). Bobot aspek penilaian
 * adalah usulan panitia karena Juknis hanya menyebut aspek tanpa bobot; nilai akhir tiap lomba dikonversi ke 100 poin.
 * Kreasi Seni Nusantara dan Festival Kuliner Daerah bukan "Lomba" sehingga hanya menjadi sesi presensi.
 */

$buper = 'Lapangan Pulo Baok, Padangsidimpuan Tenggara';
$upacara = 'Lapangan upacara perkemahan';

$days = [
    '2026-11-14' => 'Sabtu 14 November',
    '2026-11-15' => 'Minggu 15 November',
    '2026-11-16' => 'Senin 16 November',
    '2026-11-17' => 'Selasa 17 November',
];

/** Kegiatan harian berulang: [judul, hari yang berlaku, mulai, selesai] */
$daily = [
    ['Presensi Olahraga', ['2026-11-15', '2026-11-16', '2026-11-17'], '06:00', '07:00'],
    ['Presensi Apel Pagi', ['2026-11-15', '2026-11-16', '2026-11-17'], '07:00', '08:00'],
    ['Presensi Anjangsana', ['2026-11-14', '2026-11-15', '2026-11-16'], '17:00', '18:00'],
    ['Presensi Apel Sore', ['2026-11-14', '2026-11-15', '2026-11-16'], '17:30', '18:00'],
    ['Presensi Kreasi Seni Nusantara (Pentas Seni)', ['2026-11-14', '2026-11-15', '2026-11-16'], '19:30', '20:30'],
];

$dailyActivities = [];
foreach ($daily as [$title, $dates, $start, $end]) {
    foreach ($dates as $date) {
        $dailyActivities[] = [
            'title' => "{$title} — {$days[$date]}", 'type' => 'participant',
            'start' => "{$date} {$start}", 'end' => "{$date} {$end}",
        ];
    }
}

return [
    'event' => [
        'title' => 'Lomba Tingkat Regu Penggalang III (LT-III) Tahun 2026',
        'theme' => 'Tangkas, Berkarakter, Bersaudara, dan Berprestasi Menuju Generasi Indonesia Emas',
        'start_date' => '2026-11-14',
        'end_date' => '2026-11-17',
        'registration_start_at' => '2026-10-22 10:00:00',
        'registration_end_at' => '2026-11-07 17:00:00',
        'registration_fee_per_patrol' => 400000,
        'payment_bank_name' => 'Bank Sumut',
        'payment_account_number' => '2360-2030-0482-40',
        'payment_account_holder' => 'Kwarcab Kota Padangsidimpuan',
        'payment_instructions' => 'Iuran/camp fee Rp 400.000 per regu (sudah termasuk pembina pendamping) diterima Kwarcab paling lambat 25 November 2026. Kirim bukti transfer ke kwarcabpadangsidimpuan@gmail.com dengan subjek "Bukti Pembayaran LT-III Gudep". Transfer tidak dibenarkan melalui mesin ATM.',
        'location' => 'Lapangan Perkemahan Pulo Baok, Padangsidimpuan Tenggara, Kota Padangsidimpuan, Sumatera Utara',
        'description' => 'LT-III Tahun 2026 merupakan wahana pendidikan bagi Pramuka Penggalang di Kota Padangsidimpuan dalam mengintegrasikan Prinsip Dasar Kepramukaan (PDK) dan Metode Kepramukaan (MK) pada area penyeimbangan spiritual, emosional, sosial, intelektual, dan fisik (SESOSIF). Kegiatan ini menjadi media mencapai tujuan pendidikan kepramukaan, tolok ukur mutu pembinaan penggalang di Kota Padangsidimpuan, wahana eksistensi regu berprestasi di Gudep dan Ranting, serta menentukan utusan Lomba Tingkat Pramuka Penggalang Empat (LT-IV) Daerah Sumatera Utara.',
        'contact_email' => 'kwarcabpadangsidimpuan@gmail.com',
        'contact_phone' => '+62 821-6543-2100',
    ],

    // Kegiatan perkemahan (bukan lomba): menjadi sesi presensi umum
    'activities' => array_merge([
        ['title' => 'Presensi Daftar Ulang Pemimpin Regu — Sabtu 14 November', 'type' => 'participant', 'start' => '2026-11-14 08:00', 'end' => '2026-11-14 12:00'],
        ['title' => 'Presensi Upacara Pembukaan (8 orang per regu)', 'type' => 'participant', 'start' => '2026-11-14 14:00', 'end' => '2026-11-14 16:00'],
        ['title' => 'Presensi Festival Kuliner Daerah (8 orang per regu)', 'type' => 'participant', 'start' => '2026-11-17 10:00', 'end' => '2026-11-17 12:00'],
        ['title' => 'Presensi Upacara Penutupan (8 orang per regu)', 'type' => 'participant', 'start' => '2026-11-17 14:00', 'end' => '2026-11-17 16:00'],
        ['title' => 'Presensi Kehadiran Pembina Pendamping — Sabtu 14 November', 'type' => 'chaperone', 'start' => '2026-11-14 08:00', 'end' => '2026-11-14 17:00'],
    ], $dailyActivities),

    'competitions' => [
        [
            'code' => 'PION-08', 'no' => 8, 'title' => 'Lomba Pionering', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'group', 'min' => 4, 'max' => 4,
            'goal' => 'Peserta mampu membuat pionering dengan jenis konstruksi yang ditentukan oleh panitia.',
            'time' => 'Sabtu, 14 November 2026, pukul 16.00 s.d. 17.30 WIB', 'place' => $upacara,
            'steps' => [
                'Peserta merinci dan mengambil kebutuhan bahan.',
                'Peserta membangun konstruksi sesuai gambar di titik lokasi yang ditentukan panitia.',
                'Waktu pekerjaan maksimal 15 menit.',
            ],
            'attire' => 'Pakaian olahraga',
            'gear' => 'Alokasi bahan per regu (disediakan peserta): bambu, stok, dan tali pramuka.',
            'committee_gear' => 'Nomor identitas konstruksi (putra dan putri), stopwatch, kertas kerja untuk menggambar konstruksi',
            'note' => null,
            'criteria' => [['Pemanfaatan material', 20], ['Ketepatan penggunaan simpul', 20], ['Kekuatan', 25], ['Kerapian', 15], ['Kecepatan', 20]],
            'schedule' => ['day' => '2026-11-14', 'start' => '16:00', 'end' => '17:30', 'venue' => $upacara],
        ],
        [
            'code' => 'ADM-09', 'no' => 9, 'title' => 'Lomba Administrasi Regu', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'individual', 'min' => 1, 'max' => 1,
            'goal' => 'Meningkatkan semangat kemandirian dan kerja sama, serta tertib manajerial administrasi regu.',
            'time' => 'Sabtu, 14 November 2026, pukul 08.00 s.d. 10.00 WIB', 'place' => $buper,
            'steps' => [
                'Peserta membuka seluruh berkas administrasi regu.',
                'Pada saat penilaian, peserta memberikan penjelasan kepada juri.',
                'Jenis buku administrasi: Buku Anggota, Buku Keuangan, Buku Catatan Kegiatan (atau jurnal/logbook), Buku Notulen Rapat, Buku Presensi, Buku Inventaris, SKU, dan SKK.',
            ],
            'attire' => 'Seragam Pramuka', 'gear' => 'Buku administrasi regu, alat tulis', 'committee_gear' => 'Proyektor',
            'note' => 'Peserta dapat membawa dokumen administrasi regunya dalam bentuk softcopy (disajikan dengan laptop).',
            'criteria' => [['Ketepatan', 35], ['Kelengkapan', 35], ['Kerapihan', 30]],
            'schedule' => ['day' => '2026-11-14', 'start' => '08:00', 'end' => '10:00', 'venue' => $buper],
        ],
        [
            'code' => 'PACK-10', 'no' => 10, 'title' => 'Lomba Packing Pengembaraan', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'patrol', 'min' => 8, 'max' => 8,
            'goal' => 'Peserta mampu mengemas perlengkapan pribadi dan regu secara praktis.',
            'time' => 'Sabtu, 14 November 2026, pukul 10.00 s.d. 12.00 WIB', 'place' => $buper,
            'steps' => [
                'Peserta diminta menyiapkan tas dan perlengkapan untuk lomba packing.',
                'Peserta meletakkan perlengkapan dan tas di atas matras.',
                'Peserta memasukkan perlengkapan ke dalam tas sekaligus menjelaskan kepada juri dalam waktu 5 menit.',
            ],
            'attire' => 'Kaos Kegiatan',
            'gear' => 'Tas ransel volume 30 liter dan perlengkapan: pakaian, ponco, bahan makanan, obat-obatan pribadi, buku catatan dan alat tulis, peralatan makan, peralatan mandi, sleeping bag, matras (dapat di-packing di luar tas), perlengkapan ibadah. Jumlah masing-masing barang disampaikan satu hari sebelum lomba.',
            'committee_gear' => null,
            'note' => 'Lomba packing dilaksanakan saat ketibaan peserta di lokasi perkemahan. Seluruh perlengkapan pribadi dan perlengkapan regu harus dapat diangkut peserta sekaligus atau sekali jalan.',
            'criteria' => [['Ketepatan', 50], ['Kecepatan', 50]],
            'schedule' => ['day' => '2026-11-14', 'start' => '10:00', 'end' => '12:00', 'venue' => $buper],
        ],
        [
            'code' => 'SEM-11', 'no' => 11, 'title' => 'Lomba Semboyan & Isyarat (Semaphore dan Morse)', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'group', 'min' => 2, 'max' => 2,
            'goal' => 'Peserta dapat menyampaikan pesan dengan menggunakan bendera semaphore dan Morse.',
            'time' => 'Sabtu, 14 November 2026, pukul 16.00 s.d. 17.30 WIB', 'place' => $buper,
            'steps' => [
                'Peserta menerima lembar soal dan jawaban serta mengisi identitas regu.',
                '1 orang per regu mengirimkan isyarat Morse yang disampaikan dengan bunyi (peluit).',
                'Penerima isyarat Morse menuliskan isyarat yang diterima dari pengirim pada lembar jawaban.',
                'Pemberi isyarat semaphore mengirimkan pesan ke penerima (1 orang).',
                'Penerima pesan semaphore menuliskan pesan yang diterima dari pengirim pada lembar jawaban.',
            ],
            'attire' => 'Seragam Pramuka lengkap', 'gear' => 'Alat tulis, 2 set bendera semaphore, peluit Morse', 'committee_gear' => 'Laptop untuk kode Morse, lembar jawaban',
            'note' => null,
            'criteria' => [['Kecepatan', 40], ['Ketepatan pengiriman dan jawaban', 60]],
            'schedule' => ['day' => '2026-11-14', 'start' => '16:00', 'end' => '17:30', 'venue' => $buper],
        ],
        [
            'code' => 'SKT-12', 'no' => 12, 'title' => 'Lomba Sketsa Panorama', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'individual', 'min' => 1, 'max' => 1,
            'goal' => 'Peserta dapat membuat gambar sketsa panorama.',
            'time' => 'Minggu, 15 November 2026, pukul 08.00 s.d. 12.00 WIB', 'place' => $buper,
            'steps' => [
                'Panitia menentukan sasaran (dengan titik bidik) yang akan dibuat sketsa panorama.',
                'Peserta menerima lembar kerja dan mengisi identitas regu.',
                'Peserta membuat gambar sketsa panorama berdasarkan titik bidik yang ditentukan.',
                'Waktu pengerjaan maksimal 20 menit.',
            ],
            'attire' => 'Kaos Kegiatan', 'gear' => 'Kompas, alat tulis, alat bantu sketsa, papan jalan', 'committee_gear' => 'Lembar kerja ukuran A4',
            'note' => null,
            'criteria' => [['Ketepatan sudut arah yang digambar', 30], ['Ketepatan objek yang digambar', 30], ['Ketepatan teknik mengarsir gambar', 25], ['Kecepatan', 15]],
            'schedule' => ['day' => '2026-11-15', 'start' => '08:00', 'end' => '12:00', 'venue' => $buper],
        ],
        [
            'code' => 'PTP-13', 'no' => 13, 'title' => 'Lomba Peta Pita, Peta Perjalanan, Sandi dan Kompas', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'group', 'min' => 5, 'max' => 5,
            'goal' => 'Peserta dapat membuat dokumentasi perjalanan dalam bentuk peta pita dan menyalinnya ke peta perjalanan, serta dapat menggunakan sandi dan kompas.',
            'time' => 'Minggu, 15 November 2026, pukul 08.00 s.d. 12.00 WIB', 'place' => $buper,
            'steps' => [
                'Panitia menentukan rute untuk pembuatan peta pita.',
                'Peserta mengisi peta pita sesuai rute yang dilalui.',
                'Peserta menyalin peta pita ke peta perjalanan dengan skala yang ditentukan panitia menggunakan kompas.',
                'Waktu pembuatan peta perjalanan disertai pesan-pesan yang mengandung sandi, maksimal 30 menit.',
            ],
            'attire' => 'Seragam Pramuka', 'gear' => 'Kompas, alat tulis, busur derajat, penggaris, lembar peta pita, kertas milimeter, dll.', 'committee_gear' => 'Format peta pita',
            'note' => null,
            'criteria' => [['Ketepatan data peta pita', 20], ['Ketepatan rekonstruksi peta perjalanan', 20], ['Ketepatan skala', 15], ['Ketepatan dalam menggunakan arah utara pada peta', 15], ['Ketepatan dalam menggunakan tanda peta', 15], ['Ketepatan membaca sandi', 15]],
            'schedule' => ['day' => '2026-11-15', 'start' => '08:00', 'end' => '12:00', 'venue' => $buper],
        ],
        [
            'code' => 'MNK-14', 'no' => 14, 'title' => 'Lomba Menaksir', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'group', 'min' => 2, 'max' => 2,
            'goal' => 'Peserta dapat memperkirakan tinggi suatu objek.',
            'time' => 'Minggu, 15 November 2026, pukul 14.00 s.d. 17.30 WIB', 'place' => $buper,
            'steps' => [
                'Peserta menerima lembar tugas dan mengisi identitas regu.',
                'Peserta melakukan praktik pengukuran menaksir tinggi objek yang sudah ditentukan.',
                'Pengerjaan dilakukan dengan rumus/metode yang baku.',
                'Tidak boleh menggunakan perangkat/aplikasi.',
                'Peserta mengumpulkan lembar tugas.',
                'Waktu pengerjaan maksimal 20 menit.',
            ],
            'attire' => 'Kaos Kegiatan', 'gear' => 'Alat tulis dan papan jalan', 'committee_gear' => 'Lembar kerja, dan objek/benda yang ditaksir',
            'note' => null,
            'criteria' => [['Metode / cara', 50], ['Kedekatan hasil', 50]],
            'schedule' => ['day' => '2026-11-15', 'start' => '14:00', 'end' => '17:30', 'venue' => $buper],
        ],
        [
            'code' => 'P3K-15', 'no' => 15, 'title' => 'Lomba Pertolongan Pertama', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'group', 'min' => 5, 'max' => 5,
            'goal' => 'Peserta mampu menerapkan pengetahuan serta keterampilannya dalam bidang kesehatan terutama pertolongan pertama untuk mengembangkan semangat menolong sesama.',
            'time' => 'Minggu, 15 November 2026, pukul 08.00 s.d. 12.00 WIB', 'place' => $buper,
            'steps' => [
                'Satu orang peserta disimulasikan sebagai korban.',
                'Regu penolong mempersiapkan alat pertolongan.',
                'Peserta melakukan penanganan korban kecelakaan, meliputi penanganan luka dan patah tulang serta melakukan evakuasi.',
                'Regu penolong membuat tandu.',
                'Waktu penanganan luka dan patah tulang serta pembuatan tandu maksimal 20 menit.',
            ],
            'attire' => 'Seragam Pramuka', 'gear' => 'Alat (mitela, bidai, obat-obatan), tongkat regu, tali Pramuka', 'committee_gear' => 'Kertas kerja laporan',
            'note' => null,
            'criteria' => [['Prosedur dan cara penanganan korban', 40], ['Ketepatan penggunaan simpul pada tandu', 20], ['Kecepatan penanganan korban dan pembuatan tandu', 40]],
            'schedule' => ['day' => '2026-11-15', 'start' => '08:00', 'end' => '12:00', 'venue' => $buper],
        ],
        [
            'code' => 'MSK-16', 'no' => 16, 'title' => 'Lomba Masak Rimba', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'group', 'min' => 2, 'max' => 2,
            'goal' => 'Peserta dapat memasak dalam keadaan darurat.',
            'time' => 'Minggu, 15 November 2026, pukul 08.00 s.d. 12.00 WIB', 'place' => $buper,
            'steps' => [
                'Peserta menyiapkan bahan masakan berupa bahan dasar beras, bumbu dapur, dll (lauk menyesuaikan).',
                'Peserta mempersiapkan alat memasak berupa bambu dan aluminium foil (menyesuaikan).',
                'Peserta memasak bahan menggunakan alat yang telah disiapkan.',
                'Waktu memasak maksimal 45 menit.',
            ],
            'attire' => 'Scout Look', 'gear' => 'Peralatan makan, minum, pisau, kayu, sabut, korek api dan kelengkapan lainnya (tidak diperkenankan menggunakan bahan bakar minyak tanah dan gas)', 'committee_gear' => null,
            'note' => null,
            'criteria' => [['Proses memasak', 50], ['Hasil masakan', 50]],
            'schedule' => ['day' => '2026-11-15', 'start' => '08:00', 'end' => '12:00', 'venue' => $buper],
        ],
        [
            'code' => 'BVK-17', 'no' => 17, 'title' => 'Lomba Bivak', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'patrol', 'min' => 8, 'max' => 8,
            'goal' => 'Peserta dapat mendirikan shelter darurat/bivak untuk perlindungan diri dengan bahan/alat yang sederhana.',
            'time' => 'Selasa, 17 November 2026, pukul 08.00 s.d. 10.00 WIB', 'place' => 'Pulo Baok, Padangsidimpuan Tenggara',
            'steps' => [
                'Peserta membuat bivak/shelter pribadi.',
                'Peserta membawa alat dan bahan sendiri, dan diperbolehkan memanfaatkan bahan yang tersedia secara alami di lokasi kegiatan dengan tidak merusak alam.',
                'Waktu pembuatan bivak 12 menit. Pada saat penilaian, peserta memberikan penjelasan kepada juri.',
            ],
            'attire' => 'Kaos', 'gear' => 'Ponco (bukan flysheet), tali pramuka, tongkat, patok', 'committee_gear' => null,
            'note' => null,
            'criteria' => [['Fungsi', 50], ['Kekuatan', 50]],
            'schedule' => ['day' => '2026-11-17', 'start' => '08:00', 'end' => '10:00', 'venue' => 'Pulo Baok, Padangsidimpuan Tenggara'],
        ],
        [
            'code' => 'BBT-18', 'no' => 18, 'title' => 'Lomba Baris Berbaris Tongkat/Formasi Barisan', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'patrol', 'min' => 8, 'max' => 8,
            'goal' => 'Peserta mampu melakukan gerakan baris-berbaris dengan tongkat Penggalang dengan baik dan benar serta mampu melakukan formasi barisan.',
            'time' => 'Senin, 16 November 2026, pukul 14.00 s.d. 17.30 WIB', 'place' => $buper,
            'steps' => [
                'Pemimpin regu menghapalkan gerakan yang akan diberikan.',
                'Gerakan terdiri atas gerakan di tempat, gerakan berpindah tempat, dan gerakan berjalan.',
                'Aba-aba terdiri atas aba-aba lisan dan aba-aba peluit.',
                'Jumlah gerakan terdiri atas 12 gerakan.',
                'Pemimpin regu membawa regu memasuki arena selanjutnya berdiri di tempat yang ditentukan (untuk memberikan aba-aba).',
                'Setelah memasuki arena, regu tidak perlu memberikan hormat kepada juri/panitia.',
                'Arena baris-berbaris berukuran 10 x 10 meter. Waktu pelaksanaan 5 menit per regu.',
            ],
            'attire' => 'Seragam Pramuka', 'gear' => 'Tongkat Penggalang', 'committee_gear' => 'Arena, peluit',
            'note' => null,
            'criteria' => [['Aba-aba', 50], ['Ketepatan gerakan', 50]],
            'schedule' => ['day' => '2026-11-16', 'start' => '14:00', 'end' => '17:30', 'venue' => $buper],
        ],
        [
            'code' => 'SNM-19', 'no' => 19, 'title' => 'Lomba Senam Pramuka', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'patrol', 'min' => 8, 'max' => 8,
            'goal' => 'Peserta mampu melakukan gerakan Senam Pramuka dengan baik, benar dan kompak.',
            'time' => 'Senin, 16 November 2026, pukul 08.00 s.d. 12.00 WIB', 'place' => $buper,
            'steps' => [
                'Peserta tampil dengan urutan dan di lokasi yang ditentukan.',
                'Peserta melaksanakan senam pramuka.',
            ],
            'attire' => 'Pakaian olahraga', 'gear' => null, 'committee_gear' => 'Sound system, CD/MP3 Senam Pramuka, peluit',
            'note' => 'Tautan referensi: https://s.id/senampramuka-LT3',
            'criteria' => [['Ketepatan gerakan (umum dan per orang)', 50], ['Formasi', 25], ['Semangat', 25]],
            'schedule' => ['day' => '2026-11-16', 'start' => '08:00', 'end' => '12:00', 'venue' => $buper],
        ],
        [
            'code' => 'PID-20', 'no' => 20, 'title' => 'Lomba Pidato', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'individual', 'min' => 1, 'max' => 1,
            'goal' => 'Peserta mampu menyampaikan ide, gagasan dan mempengaruhi audiens dengan pidato yang baik.',
            'time' => 'Minggu, 15 November 2026, pukul 14.00 s.d. 17.30 WIB', 'place' => $buper,
            'steps' => [
                'Peserta menyampaikan pidato dengan tema yang ditentukan oleh panitia.',
                'Pilihan tema ditentukan pada tanggal 14 November 2026 melalui pengundian.',
                'Pidato menggunakan bahasa Inggris.',
                'Menyerahkan file pointer pidato kepada panitia, berisikan maksimal 7 poin (latar belakang, ide/gagasan, dll).',
                'Waktu pidato maksimal 5 menit.',
            ],
            'attire' => 'Seragam Pramuka', 'gear' => 'File pointer pidato dikirimkan ke panitia dalam format PDF. Format nama file: [Pidato] [nomor regu]', 'committee_gear' => 'Sound system, stopwatch, printer',
            'note' => null,
            'criteria' => [['Kesesuaian tema dan isi', 25], ['Sistematika penyampaian (pembuka, isi, penutup)', 20], ['Bahasa Inggris yang baik dan benar', 20], ['Vokal / artikulasi / intonasi', 20], ['Gaya dan improvisasi', 15]],
            'schedule' => ['day' => '2026-11-15', 'start' => '14:00', 'end' => '17:30', 'venue' => $buper],
        ],
        [
            'code' => 'CCR-21', 'no' => 21, 'title' => 'Lomba Cerdas Cermat', 'group' => 'Keterampilan Kepramukaan dan Bela Negara',
            'type' => 'group', 'min' => 3, 'max' => 3,
            'goal' => 'Meningkatkan pengetahuan kepramukaan.',
            'time' => 'Setiap malam, pukul 20.30 s.d. 22.00 WIB (jadwal regu: Sabtu, 14 November 2026)', 'place' => 'Lapangan Pulo Baok',
            'steps' => [
                'Setiap tim menunjuk satu juru bicara.',
                'Peserta wajib hadir 15 menit sebelum lomba dimulai. Apabila peserta tidak hadir saat pemanggilan, regu dianggap gugur.',
                'Jenis soal: soal wajib, soal lemparan, soal rebutan, dan soal penentuan.',
                'Soal wajib: setiap tim mendapat jumlah soal wajib yang sama sesuai paket soal yang dipilih. Benar nilai 100, salah nilai 0.',
                'Soal lemparan: diberikan kepada tim tertentu; jika tidak terjawab dapat dilempar ke tim lain setelahnya. Benar nilai 100, salah nilai 0.',
                'Soal rebutan: benar nilai 100, salah dikurang 50. Semua tim berhak menekan bel atau mengangkat tanda sesuai aturan.',
                'Soal penentuan: digunakan jika ada tim memperoleh nilai sama.',
            ],
            'attire' => 'Seragam Pramuka lengkap', 'gear' => null, 'committee_gear' => null,
            'note' => 'Bobot aspek penilaian merupakan usulan panitia untuk konversi perolehan nilai soal ke 100 poin.',
            'criteria' => [['Soal wajib', 50], ['Soal lemparan', 20], ['Soal rebutan', 30]],
            'schedule' => ['day' => '2026-11-14', 'start' => '20:30', 'end' => '22:00', 'venue' => 'Lapangan Pulo Baok'],
        ],
        [
            'code' => 'POS-22', 'no' => 22, 'title' => 'Lomba Melukis Poster', 'group' => 'Keterampilan Hidup dan Manajerial',
            'type' => 'individual', 'min' => 1, 'max' => 1,
            'goal' => 'Meningkatkan apresiasi seni lukis dengan menggunakan cat akrilik dan media kanvas.',
            'time' => 'Minggu, 15 November 2026, pukul 14.00 s.d. 17.30 WIB', 'place' => $buper,
            'steps' => [
                'Peserta menyiapkan kanvas pada saat lomba.',
                'Peserta melukis dengan tema “Anti Perundungan”.',
                'Waktu melukis 120 menit.',
            ],
            'attire' => 'Kaos Kegiatan', 'gear' => 'Kuas dan sarana penunjang lainnya disiapkan sendiri oleh peserta', 'committee_gear' => 'Cat akrilik 12 warna dan kanvas ukuran 50 x 80 cm (disediakan oleh peserta)',
            'note' => null,
            'criteria' => [['Pesan (sesuai tema)', 40], ['Komposisi', 30], ['Kreativitas', 30]],
            'schedule' => ['day' => '2026-11-15', 'start' => '14:00', 'end' => '17:30', 'venue' => $buper],
        ],
        [
            'code' => 'HSK-23', 'no' => 23, 'title' => 'Lomba Hasta Karya', 'group' => 'Keterampilan Hidup dan Manajerial',
            'type' => 'group', 'min' => 2, 'max' => 2,
            'goal' => 'Menumbuhkan jiwa mental wirausaha dan mampu membaca peluang usaha dengan kegiatan hasta karya.',
            'time' => 'Minggu, 15 November 2026, pukul 14.00 s.d. 17.30 WIB', 'place' => $buper,
            'steps' => [
                'Peserta membuat hasta karya dengan bahan limbah plastik (botol minuman, kemasan sachet sabun/sampo/pasta gigi dan bahan sejenisnya), kardus, bambu/kayu, kain perca, yang dibawa dalam jumlah yang cukup.',
                'Peserta membuat 2 jenis hasta karya: alat rumah tangga (fungsional) dan hiasan (dekorasi).',
                'Bahan-bahan sudah dalam keadaan bersih dan siap rakit (dijahit, dilem, dipaku, dll); misalnya bambu sudah siap rakit namun belum berbentuk jadi.',
                'Hasil karya ditempatkan di lokasi yang ditentukan panitia/juri dan diberi identitas oleh panitia.',
                'Pada saat pembuatan dan penilaian, peserta memberikan penjelasan kepada juri.',
                'Waktu pekerjaan 150 menit.',
            ],
            'attire' => 'Kaos Kegiatan', 'gear' => 'Alat dan bahan sesuai rencana peserta. Apabila menggunakan alat listrik, peserta membawa sendiri kabelnya.', 'committee_gear' => 'Sound system',
            'note' => null,
            'criteria' => [['Nilai guna', 30], ['Kreativitas', 30], ['Nilai ekonomi', 25], ['Ketepatan waktu', 15]],
            'schedule' => ['day' => '2026-11-15', 'start' => '14:00', 'end' => '17:30', 'venue' => $buper],
        ],
        [
            'code' => 'REP-24', 'no' => 24, 'title' => 'Lomba Reportase', 'group' => 'Keterampilan Hidup dan Manajerial',
            'type' => 'group', 'min' => 2, 'max' => 2,
            'goal' => 'Meningkatkan kemampuan peserta dalam komunikasi publik melalui pemberitaan kegiatan.',
            'time' => 'Minggu, 15 November 2026, pukul 14.00 s.d. 17.30 WIB', 'place' => 'Seluruh areal LT-III 2026',
            'steps' => [
                'Peserta melakukan reportase untuk menghasilkan 3 jenis produk: video, tulisan berita, dan foto (dari satu jenis kegiatan yang diliput).',
                'Kegiatan yang dapat diliput: Lomba Lukis, Lomba Pidato dan Lomba Hasta Karya.',
                'Saat melakukan liputan, reporter tidak mengganggu peserta lomba yang diliput.',
                'Video: durasi maksimal 180 detik, rasio 16:9 landscape resolusi minimal 720p (1280x720), aplikasi edit Kinemaster/Capcut/Canva, format MP4, nama file [Nomor Regu] [Video].',
                'Tulisan: maksimal 100 kata, font Arial 12 poin spasi 1,5, format PDF, nama file [Nomor Regu] [Artikel].',
                'Foto: 1 buah tanpa edit, format jpg/jpeg, nama file [Nomor Regu] [Foto].',
                'Tiga file hasil liputan diunggah ke Google Drive yang diinformasikan panitia saat kegiatan.',
            ],
            'attire' => 'Seragam Pramuka', 'gear' => 'Handphone, mic, tripod, dll.', 'committee_gear' => 'Sound system, LCD Projector, screen dan laptop',
            'note' => null,
            'criteria' => [['Ide dan gagasan yang kreatif', 30], ['Penggunaan bahasa', 25], ['Orisinalitas', 25], ['Kelengkapan unsur peliputan', 20]],
            'schedule' => ['day' => '2026-11-15', 'start' => '14:00', 'end' => '17:30', 'venue' => 'Seluruh areal LT-III 2026'],
        ],
    ],
];
