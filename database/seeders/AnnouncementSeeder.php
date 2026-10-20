<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder pengumuman resmi LT III
 */
class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();

        $announcements = [
            [
                'title' => 'Surat Edaran Resmi Penyelenggaraan LT III Kwarcab Padangsidimpuan',
                'slug' => 'surat-edaran-resmi-penyelenggaraan-lt-iii-kwarcab-padangsidimpuan',
                'category' => 'general',
                'summary' => 'Pemberitahuan kepada seluruh Ka. Mabigus dan Pembina Gugus Depan pangkalan SMP/MTs se-Kota Padangsidimpuan terkait pelaksanaan LT III.',
                'content' => "Salam Pramuka,\n\nDiberitahukan dengan hormat kepada seluruh Ketua Majelis Pembimbing Gugus Depan (Ka. Mabigus) dan Pembina Pramuka Penggalang se-Kwartir Cabang Kota Padangsidimpuan bahwa penyelenggaraan Lomba Tingkat Regu Pramuka Penggalang Tiga (LT III) Tahun ini akan segera dilaksanakan.\n\nSetiap Gugus Depan berhak mendaftarkan regu terbaiknya (1 Regu Penggalang Putra dan 1 Regu Penggalang Putri). Pendaftaran dilakukan secara terpadu melalui aplikasi web resmi ini oleh perwakilan resmi Gugus Depan.\n\nDemikian edaran ini disampaikan, atas perhatian dan partisipasinya diucapkan terima kasih.",
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Petunjuk Teknis (Juknis) dan Tata Tertib Lomba Tingkat III Resmi Dirilis',
                'slug' => 'petunjuk-teknis-juknis-dan-tata-tertib-lt-iii-resmi-dirilis',
                'category' => 'technical',
                'summary' => 'Juknis memuat kriteria penilaian, perlengkapan lomba, alur presensi QR code, dan pedoman perkemahan.',
                'content' => "Petunjuk Teknis (Juknis) resmi LT III Kwarcab Padangsidimpuan kini dapat diakses dan diunduh melalui menu Mata Lomba & Juknis.\n\nSeluruh pangkalan, pembina pendamping, dan peserta diimbau mempelajari secara teliti rincian rubrik penilaian, durasi waktu per mata lomba, serta ketentuan presensi berbasis QR Code di setiap arena lomba.\n\nPerhatikan bahwa absensi pos dibuka tepat 30 menit sebelum jadwal lomba dimulai menggunakan waktu server Asia/Jakarta (WIB).",
                'is_published' => true,
                'published_at' => now()->subDay(),
            ],
            [
                'title' => 'Jadwal Technical Meeting (Temu Teknis) Pembina Pendamping dan Juri',
                'slug' => 'jadwal-technical-meeting-temu-teknis-pembina-pendamping-dan-juri',
                'category' => 'schedule',
                'summary' => 'Pelaksanaan temu teknis penetapan nomor undian kavling tenda dan konfirmasi teknis cabang lomba.',
                'content' => "Temu Teknis (Technical Meeting) akan diselenggarakan di Aula Kwarcab Gerakan Pramuka Kota Padangsidimpuan pada H-2 pembukaan kegiatan.\n\nAgenda meliputi:\n1. Pengesahan nomor kavling tenda putra dan putri.\n2. Penjelasan sistem penilaian blind scoring bagi dewan juri.\n3. Uji coba pemindaian QR Code kartu peserta digital.\n\nKehadiran Pembina Pendamping (Bindamping) bersifat wajib.",
                'is_published' => true,
                'published_at' => now(),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['author_id' => $admin?->id])
            );
        }
    }
}
