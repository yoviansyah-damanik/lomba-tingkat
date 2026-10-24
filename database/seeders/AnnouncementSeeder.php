<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

/**
 * Seeder pengumuman resmi LT-III Tahun 2026 (bersumber dari Petunjuk Pelaksanaan No. 12 dan Petunjuk Teknis No. 14 Tahun 2026)
 */
class AnnouncementSeeder extends Seeder
{
    /** Slug pengumuman versi draf Juknis yang digantikan data final */
    private const LEGACY_SLUGS = [
        'jadwal-inti-lt-iii-tahun-2026-27-29-november',
        'pengundian-tema-lomba-pidato-dan-pengiriman-file-pointer',
        'tata-tertib-perkemahan-dan-perlengkapan-regu',
    ];

    public function run(): void
    {
        Announcement::whereIn('slug', self::LEGACY_SLUGS)->delete();

        foreach ($this->announcements() as $data) {
            Announcement::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function announcements(): array
    {
        return [
            [
                'title' => 'Petunjuk Pelaksanaan dan Petunjuk Teknis LT-III Tahun 2026 Resmi Ditetapkan',
                'slug' => 'petunjuk-teknis-lt-iii-tahun-2026-resmi-dirilis',
                'category' => 'technical',
                'summary' => 'Juklak No. 12 dan Juknis No. 14 Tahun 2026 memuat kehidupan perkemahan, 17 mata lomba, ketentuan regu berprestasi, manajemen risiko, dan perlindungan anggota.',
                'content' => "Petunjuk Pelaksanaan (Juklak) Nomor 12 Tahun 2026 dan Petunjuk Teknis (Juknis) Nomor 14 Tahun 2026 Lomba Tingkat Regu Pramuka Penggalang Tiga (LT-III) Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan telah ditetapkan dan menjadi pedoman bagi panitia, juri, peserta, pembina pendamping, dan pimpinan kontingen.\n\nLT-III Tahun 2026 dilaksanakan pada 14 s.d. 17 November 2026 di Lapangan Perkemahan Pulo Baok, Padangsidimpuan Tenggara.\n\nTema: \"Tangkas, Berkarakter, Bersaudara, dan Berprestasi Menuju Generasi Indonesia Emas\".\nMotto: \"Satyaku kudarmakan, Darmaku kubaktikan\".\n\nRuang lingkup Juknis:\n1. Pendahuluan\n2. Kehidupan Perkemahan\n3. Kegiatan\n4. Kehumasan dan Pendukung\n5. Manajemen Risiko, Perlindungan Anggota, Penelitian dan Evaluasi\n6. Penutup\n\nRincian ketentuan teknis setiap mata lomba (tujuan, waktu, tempat, peserta, prosedur, aspek penilaian, pakaian, dan perlengkapan) dapat dibuka pada menu Mata Lomba & Juknis. Hal yang belum diatur akan ditetapkan kemudian oleh Kwartir Cabang.",
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Pendaftaran LT-III Tahun 2026: 22 Oktober s.d. 7 November 2026',
                'slug' => 'pendaftaran-lt-iii-tahun-2026-22-oktober-7-november',
                'category' => 'general',
                'summary' => 'Pendaftaran melalui website resmi pukul 10.00-17.00 WIB; iuran Rp 400.000 per regu; daftar ulang 14 November 2026.',
                'content' => "Tahapan pendaftaran:\n\n1. Tahap I - Pendaftaran gugusdepan\nMelalui website resmi https://lt3kwarcabpadangsidimpuan.my.id/ pada 22 Oktober s.d. 7 November 2026, pukul 10.00-17.00 WIB. Batas akhir 7 November 2026 pukul 17.00 WIB.\n\n2. Tahap II - Pembayaran iuran\nIuran/camp fee Rp 400.000 per regu (sudah termasuk pembina pendamping) ditransfer ke Bank Sumut, nomor rekening 2360-2030-0482-40 a.n. Kwarcab Kota Padangsidimpuan. Iuran diterima paling lambat 25 November 2026 dan bukti transfer dikirim ke kwarcabpadangsidimpuan@gmail.com dengan subjek \"Bukti Pembayaran LT-III Gudep\". Transfer tidak dibenarkan melalui mesin ATM.\n\n3. Tahap III - Daftar ulang\nDilakukan Pemimpin Regu di lokasi kegiatan pada Sabtu, 14 November 2026, pukul 08.00-12.00 WIB.",
                'is_published' => true,
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Ketentuan Kontingen: Regu Utuh Berprestasi Tinggi LT-I dan Persyaratan Peserta',
                'slug' => 'ketentuan-kontingen-regu-utuh-dan-persyaratan-peserta',
                'category' => 'general',
                'summary' => 'Setiap gugusdepan mengirim satu regu putra dan satu regu putri (8 orang) beserta 1 pembina pendamping tetap.',
                'content' => "Ketentuan kontingen gugusdepan:\n- Mengirim satu regu putra dan satu regu putri yang merupakan regu utuh Berprestasi pada LT-I dan LT-II serta Berprestasi Tinggi pada LT-I, dibuktikan dengan SK, piagam, dan foto kegiatan.\n- Setiap regu beranggotakan 8 orang Pramuka Penggalang berusia 11-15 tahun dan belum berusia 16 tahun pada Juni 2027.\n- Penggantian personil hanya atas alasan meninggal dunia atau pindah domisili dan dicatat dalam buku registrasi/logbook regu.\n- Setiap regu didampingi 1 orang pembina pendamping tetap dari gugusdepan yang sama.\n- Regu membawa surat tugas dan SK Regu Berprestasi Tinggi LT-I.\n\nPersyaratan administratif peserta: KTA, buku SKU dan piagam SKK, kartu asuransi kesehatan/BPJS yang berlaku, surat izin orang tua/wali, dan surat keterangan sehat dari dokter.\n\nPersyaratan pembina pendamping: KTA, Surat Hak Bina (SHB) yang berlaku, kartu asuransi kesehatan/BPJS, surat keterangan sehat, sertifikat vaksin, dan sertifikat Safe from Harm.",
                'is_published' => true,
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Teknikal Meeting LT-III: Sabtu 24 Oktober 2026 di Kantor Kwarcab',
                'slug' => 'teknikal-meeting-lt-iii-sabtu-24-oktober-2026',
                'category' => 'schedule',
                'summary' => 'Dihadiri Pinru dan pendamping (putra dan putri) pukul 14.00 WIB; penyerahan kelengkapan administrasi dan undian tapak kemah.',
                'content' => "Teknikal meeting, penyerahan kelengkapan administrasi, dan pembagian tugas:\n- Hari/tanggal: Sabtu, 24 Oktober 2026\n- Waktu: 14.00 WIB s.d. selesai\n- Tempat: Kantor Kwarcab Padangsidimpuan\n- Peserta: Pemimpin Regu dan Pembina Pendamping (putra dan putri)\n- Pakaian: Pramuka\n\nPada kesempatan ini dilakukan pengundian nomor tapak perkemahan dan pembagian pasukan. Regu yang tidak hadir menerima nomor undian saat registrasi di lokasi kegiatan. Tapak kemah tidak boleh diperluas dari ukuran yang ditentukan (5 x 5 meter per regu).",
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Jadwal Inti LT-III Tahun 2026: Sabtu 14 s.d. Selasa 17 November 2026',
                'slug' => 'jadwal-inti-lt-iii-tahun-2026-14-17-november',
                'category' => 'schedule',
                'summary' => 'Daftar ulang dan upacara pembukaan 14 November; upacara penutupan 17 November 2026 pukul 14.00 WIB.',
                'content' => "Jadwal umum:\n- Sabtu, 14-11-2026: daftar ulang 08.00-12.00 WIB; Lomba Administrasi Regu 08.00-10.00; Lomba Packing Pengembaraan 10.00-12.00; Upacara Pembukaan 14.00-16.00 (8 orang per regu); Lomba Pionering dan Lomba Semboyan & Isyarat 16.00-17.30; pentas seni/lomba malam hari.\n- Minggu, 15-11-2026: Sketsa Panorama, Peta Pita, Pertolongan Pertama, dan Masak Rimba 08.00-12.00; Menaksir, Pidato, Melukis Poster, Hasta Karya, dan Reportase 14.00-17.30.\n- Senin, 16-11-2026: Lomba Senam Pramuka 08.00-12.00; Lomba Baris Berbaris Tongkat/Formasi Barisan 14.00-17.30.\n- Selasa, 17-11-2026: Lomba Bivak 08.00-10.00; Festival Kuliner Daerah 10.00-12.00; Upacara Penutupan 14.00 WIB.\n\nKegiatan harian: olahraga 06.00-07.00, apel pagi 07.00-08.00, anjangsana 17.00-18.00, apel sore 17.30-18.00, Kreasi Seni Nusantara 19.30-20.30, dan Lomba Cerdas Cermat setiap malam 20.30-22.00 WIB. Jadwal Permainan Persaudaraan menyusul.\n\nLomba dilaksanakan dalam tiga pembagian waktu: Pagi (07.30-12.00 WIB), Siang/Sore (14.00-18.00 WIB), dan Malam (19.30-22.00 WIB). Setiap regu wajib memperhatikan jadwal dan hadir 15 menit sebelum lomba dimulai tanpa pemanggilan panitia. Jadwal setiap regu dapat dilihat pada menu Jadwal.",
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Wajib: Kursus Safe from Harm bagi Seluruh Warga Perkemahan di Atas 18 Tahun',
                'slug' => 'wajib-kursus-safe-from-harm-warga-perkemahan-di-atas-18-tahun',
                'category' => 'technical',
                'summary' => 'Sertifikat kursus ditunjukkan melalui ponsel saat memasuki bumi perkemahan.',
                'content' => "Sebelum memasuki area kegiatan, seluruh warga perkemahan yang berusia di atas 18 tahun tanpa terkecuali wajib mengikuti Pelatihan Perlindungan Anggota (Safe from Harm).\n\nLangkah:\n1. Ikuti Kursus (E-Learning) Pengetahuan Dasar tentang Safe from Harm berstandar WOSM dalam Bahasa Indonesia di http://bit.ly/KursusSafeFromHarm\n2. Tunjukkan sertifikat kursus pada saat memasuki Bumi Perkemahan (tidak perlu dicetak, dapat ditunjukkan melalui ponsel).\n3. Baca dan pahami Jukran Kwarnas No. 04 Tahun 2021 tentang Peraturan Perlindungan Anggota Gerakan Pramuka (https://pramuka.or.id/files/document/Jukran-Kwarnas-004-2021-Safe-From-Harm.pdf).\n\nLayanan konseling \"Kakak Pendengar\" tersedia bagi seluruh peserta selama kegiatan, dan laporan pelanggaran (perundungan, kekerasan, pelecehan, dan sejenisnya) dijamin kerahasiaannya.",
                'is_published' => true,
                'published_at' => now()->subDay(),
            ],
            [
                'title' => 'Lomba Pidato: Bahasa Inggris, Undian Tema 14 November 2026, dan File Pointer PDF',
                'slug' => 'lomba-pidato-bahasa-inggris-undian-tema-dan-file-pointer',
                'category' => 'technical',
                'summary' => 'Tema ditentukan melalui pengundian pada 14 November 2026; pidato berbahasa Inggris maksimal 5 menit.',
                'content' => "Ketentuan Lomba Pidato (Minggu, 15 November 2026, 14.00-17.30 WIB):\n- Pilihan tema ditentukan pada 14 November 2026 melalui pengundian.\n- Pidato menggunakan bahasa Inggris, maksimal 5 menit.\n- File pointer pidato berisi maksimal 7 poin (latar belakang, ide/gagasan, dll) dikirim kepada panitia dalam format PDF dengan nama file [Pidato] [nomor regu].\n\nKetentuan lain:\n- Lomba Melukis Poster bertema \"Anti Perundungan\" (120 menit); kanvas ukuran 50 x 80 cm.\n- Peserta Kreasi Seni Nusantara mengonfirmasi kebutuhan dan mengirim file musik selambatnya 1 hari sebelum penampilan (maksimal 7 menit, gabungan regu putra dan putri).\n- Lomba Reportase menghasilkan video, tulisan berita, dan foto dari satu kegiatan yang diliput, diunggah ke Google Drive yang diinformasikan panitia.",
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'Penilaian dan Penentuan Regu Berprestasi: Konversi Poin per Peringkat',
                'slug' => 'penilaian-dan-penentuan-regu-berprestasi',
                'category' => 'technical',
                'summary' => 'Peringkat setiap mata lomba dikonversi menjadi poin; regu dengan poin tertinggi menjadi Regu Berprestasi Tinggi.',
                'content' => "Setiap mata lomba dinilai juri dengan nilai tertinggi 100 poin. Peringkat pada tiap mata lomba dikonversi menjadi poin:\n1 (Tinggi) = 100; 2 (Baik) = 80; 3 (Cukup) = 70; 4 = 65; 5 = 60; 6 = 55; 7 = 54; 8 = 53; 9 = 52; 10 = 51; 11-20 = 50; 21-33 = 45.\n\nRegu Berprestasi terdiri atas Prestasi Tinggi, Baik, dan Cukup, masing-masing untuk putra dan putri, ditentukan dari akumulasi poin seluruh kegiatan. Bila poin total sama, penentu adalah jumlah lomba dengan prestasi Tinggi, kemudian Baik, kemudian Cukup.\n\nRegu Prestasi Tinggi putra dan putri ditetapkan sebagai utusan Kwarcab Kota Padangsidimpuan pada Lomba Tingkat IV Daerah Sumatera Utara. Setiap hari diberikan wimpel kepada regu dengan nilai tertinggi pada tiap mata lomba. Hasil penilaian juri tidak dapat diganggu gugat; sanggahan hanya dapat diajukan Pembina/Pendamping paling lambat 15 menit setelah mata lomba selesai.",
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'Tata Tertib Perkemahan dan Perlengkapan yang Wajib Dibawa Regu',
                'slug' => 'tata-tertib-perkemahan-dan-perlengkapan-regu',
                'category' => 'general',
                'summary' => 'ID Card wajib dipakai, area perkemahan bebas asap rokok, dan setiap regu membawa tenda serta perlengkapan masak sendiri.',
                'content' => "Aturan umum perkemahan:\n- Seluruh warga perkemahan wajib memakai ID Card LT-III Tahun 2026.\n- Kendaraan bermotor tidak diperbolehkan memasuki areal perkemahan kecuali kendaraan bertanda pengenal panitia (ambulans, pengangkut sampah, konsumsi/natura, air, panitia, pemadam kebakaran).\n- Area perkemahan bebas dari asap rokok; dilarang membakar sampah.\n- Sampah dipilah dan dikumpulkan setiap pukul 06.00-07.00 WIB.\n- Peserta meninggalkan perkemahan hanya atas izin Pembina Pasukan.\n- Selain peserta dan satu pembina pendamping per regu, tidak diperkenankan memasuki lokasi perkemahan dan lokasi lomba. Pembina pendamping tidak boleh memberikan intervensi yang menguntungkan regu.\n- Tapak kemah 5 x 5 meter per regu; dilarang membuat gapura/pagar, menebang pohon, menyambung listrik, atau menggali lubang untuk tiang bendera dan jemuran.\n\nPerlengkapan: setiap regu membawa minimal 2 tenda regu serta tenda dapur/makan, perlengkapan masak, lampu tenda, laptop 1 unit, dan kotak P3K; setiap bindamping membawa tenda individu. Seluruh perlengkapan harus dapat diangkut sekali jalan.\n\nKonsumsi ditanggung peserta (natura dimasak di tenda dapur regu); panitia tidak menyiapkan penjemputan maupun pengantaran. Warga perkemahan dilarang membawa perhiasan atau barang berharga berlebihan.",
                'is_published' => true,
                'published_at' => now(),
            ],
        ];
    }
}
