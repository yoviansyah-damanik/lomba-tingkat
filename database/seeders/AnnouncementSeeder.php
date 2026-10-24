<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

/**
 * Seeder pengumuman resmi LT-III Tahun 2026 (bersumber dari Petunjuk Teknis)
 */
class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                'title' => 'Petunjuk Teknis LT-III Tahun 2026 Kwarcab Padangsidimpuan Resmi Dirilis',
                'slug' => 'petunjuk-teknis-lt-iii-tahun-2026-resmi-dirilis',
                'category' => 'technical',
                'summary' => 'Juknis memuat kehidupan perkemahan, 23 mata lomba, ketentuan regu berprestasi, manajemen risiko, dan perlindungan anggota.',
                'content' => "Petunjuk Teknis (Juknis) Lomba Tingkat Regu Penggalang III (LT-III) Tahun 2026 Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan telah ditetapkan dan menjadi pedoman bagi panitia, juri, peserta, pembina pendamping, dan pimpinan kontingen.\n\nRuang lingkup Juknis:\n1. Pendahuluan\n2. Kehidupan Perkemahan\n3. Kegiatan\n4. Kehumasan dan Pendukung\n5. Manajemen Risiko, Perlindungan Anggota, Penelitian dan Evaluasi\n6. Penutup\n\nRincian ketentuan teknis setiap mata lomba (tujuan, waktu, tempat, peserta, prosedur, aspek penilaian, pakaian, dan perlengkapan) dapat dibuka pada menu Mata Lomba & Juknis.",
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Jadwal Inti LT-III Tahun 2026: Jumat 27 s.d. Minggu 29 November 2026',
                'slug' => 'jadwal-inti-lt-iii-tahun-2026-27-29-november',
                'category' => 'schedule',
                'summary' => 'Upacara pembukaan, permainan persaudaraan, Lomba Pionering, Administrasi Regu, dan Packing Pengembaraan.',
                'content' => "Jadwal kegiatan yang telah ditetapkan Juknis:\n\n- Jumat, 27-11-2026, 14.00-15.00 WIB: Upacara Pembukaan (8 orang per regu)\n- Jumat, 27-11-2026, 15.00-16.00 WIB: Permainan Persaudaraan (8 orang per regu)\n- Jumat, 27-11-2026, 16.00-18.00 WIB: Lomba Pionering (6 orang) dan Lomba Administrasi Regu (2 orang)\n- Sabtu, 28-11-2026, 08.00-17.00 WIB: Lomba Packing Pengembaraan bersamaan dengan Lomba Semboyan dan Isyarat\n\nKegiatan harian: olahraga pukul 06.00-07.00 WIB, apel pagi pukul 07.00-08.00 WIB, anjangsana pukul 17.00-18.00 WIB, dan apel sore pukul 17.30-18.00 WIB.\n\nLomba dilaksanakan dalam tiga pembagian waktu: Pagi (07.30-12.00 WIB), Siang/Sore (14.00-18.00 WIB), dan Malam (19.30-22.00 WIB). Jadwal setiap regu dapat dilihat pada menu Jadwal.",
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Wajib: Kursus Safe from Harm bagi Seluruh Warga Perkemahan di Atas 18 Tahun',
                'slug' => 'wajib-kursus-safe-from-harm-warga-perkemahan-di-atas-18-tahun',
                'category' => 'technical',
                'summary' => 'Sertifikat kursus ditunjukkan melalui ponsel saat memasuki bumi perkemahan.',
                'content' => "Sebelum memasuki area kegiatan, seluruh warga perkemahan yang berusia di atas 18 tahun tanpa terkecuali wajib mengikuti Pelatihan Perlindungan Anggota (Safe from Harm).\n\nLangkah:\n1. Ikuti Kursus (E-Learning) Pengetahuan Dasar tentang Safe from Harm berstandar WOSM dalam Bahasa Indonesia di http://bit.ly/KursusSafeFromHarm\n2. Tunjukkan sertifikat kursus pada saat memasuki Bumi Perkemahan (tidak perlu dicetak, dapat ditunjukkan melalui ponsel).\n3. Baca dan pahami Jukran Kwarnas No. 04 Tahun 2021 tentang Peraturan Perlindungan Anggota Gerakan Pramuka.\n\nLayanan konseling \"Kakak Pendengar\" tersedia bagi seluruh peserta selama kegiatan.",
                'is_published' => true,
                'published_at' => now()->subDay(),
            ],
            [
                'title' => 'Pengundian Tema Lomba Pidato dan Pengiriman File Pointer',
                'slug' => 'pengundian-tema-lomba-pidato-dan-pengiriman-file-pointer',
                'category' => 'technical',
                'summary' => 'Tema ditentukan melalui pengundian pada 23 November 2026; file pointer dikirim dalam format PDF.',
                'content' => "Pilihan tema Lomba Pidato ditentukan melalui pengundian pada tanggal 23 November 2026.\n\nKetentuan:\n- Pidato menggunakan bahasa Indonesia, maksimal 5 menit.\n- File pointer pidato berisi maksimal 7 poin (latar belakang, ide/gagasan, dll) dikirim kepada panitia dalam format PDF.\n- Format nama file: [Pidato] [nomor regu].\n\nKetentuan lain: peserta mengirim kebutuhan dan file musik Kreasi Seni Nusantara selambatnya 1 hari sebelum penampilan.",
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'Tata Tertib Perkemahan dan Perlengkapan yang Wajib Dibawa Regu',
                'slug' => 'tata-tertib-perkemahan-dan-perlengkapan-regu',
                'category' => 'general',
                'summary' => 'ID Card wajib dipakai, area perkemahan bebas asap rokok, dan setiap regu membawa tenda serta perlengkapan masak sendiri.',
                'content' => "Aturan umum perkemahan:\n- Seluruh warga perkemahan wajib memakai ID Card LT-III Tahun 2026.\n- Kendaraan bermotor tidak diperbolehkan memasuki areal perkemahan kecuali kendaraan bertanda pengenal panitia (ambulans, pengangkut sampah, konsumsi, air, panitia, pemadam kebakaran).\n- Area perkemahan bebas dari asap rokok; dilarang membakar sampah.\n- Sampah dipilah dan dikumpulkan setiap pukul 06.00-07.00 WIB.\n- Peserta meninggalkan perkemahan hanya atas izin Pembina Pasukan.\n\nPerlengkapan: setiap regu membawa minimal 2 tenda regu serta tenda dapur/makan, perlengkapan masak, dan lampu tenda; setiap bindamping membawa tenda individu. Konsumsi ditanggung peserta; panitia tidak menyiapkan konsumsi maupun penjemputan.",
                'is_published' => true,
                'published_at' => now(),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
