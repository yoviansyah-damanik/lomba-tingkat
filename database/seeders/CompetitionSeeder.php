<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk cabang mata lomba resmi LT III dengan kriteria peserta (Individu vs Regu dan interval jumlah)
 */
class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::first();

        $competitions = [
            [
                'code' => 'PBB-01',
                'title' => 'PBB Berisyarat dan Koloni Tongkat',
                'category' => 'all',
                'participant_type' => 'patrol',
                'min_participants' => 8,
                'max_participants' => 8,
                'description' => 'Perlombaan Peraturan Baris Berbaris dasar dan variasi formasi menggunakan tongkat pramuka dengan aba-aba peluit, isyarat tangan, dan suara.',
                'guidelines_text' => 'Durasi penampilan maksimal 8 menit. Komposisi tepat 8 orang anggota regu termasuk Pimpinan Regu.',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
            [
                'code' => 'PION-02',
                'title' => 'Pionering Bangunan Darurat (Menara Pandang)',
                'category' => 'all',
                'participant_type' => 'group',
                'min_participants' => 3,
                'max_participants' => 5,
                'description' => 'Pembangunan konstruksi menara pandang kaki tiga menggunakan tongkat bambu dan tali rami yang kokoh dan estetis.',
                'guidelines_text' => 'Maksimal 30 tongkat bambu. Durasi perakitan 60 menit. Diikuti oleh 3 hingga 5 orang peserta per regu.',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
            [
                'code' => 'SEM-03',
                'title' => 'Semboyan & Isyarat (Semaphore, Morse, Sandi)',
                'category' => 'all',
                'participant_type' => 'group',
                'min_participants' => 3,
                'max_participants' => 3,
                'description' => 'Uji ketangkasan penerimaan dan pengiriman berita rahasia menggunakan bendera semaphore, peluit morse, dan pemecahan sandi pramuka.',
                'guidelines_text' => 'Diikuti oleh 3 orang utusan per regu. Terdiri dari pos Semaphore (1 orang), Morse (1 orang), dan Sandi (1 orang).',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
            [
                'code' => 'NAV-04',
                'title' => 'Navigasi Darat, Kompas Bidik & Peta Pita',
                'category' => 'all',
                'participant_type' => 'group',
                'min_participants' => 4,
                'max_participants' => 6,
                'description' => 'Penjelajahan rute alam bebas dengan membuat peta pita, peta panorama, serta pembidikan sudut azimut kompas bidik prisma.',
                'guidelines_text' => 'Rute sejauh 5 km dengan 4 pos pengecekan mandiri. Diikuti oleh interval 4 sampai 6 orang peserta per regu.',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
            [
                'code' => 'PPGD-05',
                'title' => 'Pertolongan Pertama Pada Kecelakaan (P3K / PPGD)',
                'category' => 'all',
                'participant_type' => 'group',
                'min_participants' => 4,
                'max_participants' => 4,
                'description' => 'Simulasi penanganan korban cedera patah tulang, evakuasi tandu darurat, dan resusitasi jantung paru dasar.',
                'guidelines_text' => 'Waktu penanganan maksimal 15 menit. Diikuti oleh tepat 4 orang (1 korban, 3 penolong).',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
            [
                'code' => 'SENI-06',
                'title' => 'Pentas Seni Budaya Tapanuli Bagian Selatan',
                'category' => 'all',
                'participant_type' => 'group',
                'min_participants' => 5,
                'max_participants' => 8,
                'description' => 'Penampilan kreasi seni tari, tortor, atau musik etnik kearifan lokal Padangsidimpuan / Tapanuli Selatan.',
                'guidelines_text' => 'Durasi 5-7 menit di panggung utama malam api unggun. Diikuti oleh 5 hingga 8 orang peserta per regu.',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
            [
                'code' => 'MASAK-07',
                'title' => 'Masak Rimba & Kuliner Tradisional',
                'category' => 'all',
                'participant_type' => 'group',
                'min_participants' => 2,
                'max_participants' => 3,
                'description' => 'Memasak makanan bergizi seimbang di alam terbuka menggunakan peralatan sederhana dan bahan pangan lokal.',
                'guidelines_text' => 'Durasi 60 menit. Diikuti oleh 2 hingga 3 orang juru masak per regu.',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
            [
                'code' => 'CCK-08',
                'title' => 'Cerdas Cermat Kepramukaan & Umum (CCK)',
                'category' => 'all',
                'participant_type' => 'group',
                'min_participants' => 3,
                'max_participants' => 3,
                'description' => 'Uji wawasan seputar AD/ART Gerakan Pramuka, sejarah kepanduan dunia, dan wawasan kebangsaan.',
                'guidelines_text' => 'Diikuti oleh regu inti 3 orang (1 juru bicara, 2 pendamping).',
                'scoring_formula' => 'sum',
                'is_published' => true,
            ],
            [
                'code' => 'PID-09',
                'title' => 'Pidato Kebangsaan & Kepramukaan',
                'category' => 'all',
                'participant_type' => 'individual',
                'min_participants' => 1,
                'max_participants' => 1,
                'description' => 'Lomba orasi pidato bertema kepramukaan dan cinta tanah air yang dibawakan secara perorangan oleh utusan regu.',
                'guidelines_text' => 'Diikuti oleh 1 orang peserta per regu (Individu). Durasi pidato 5 menit tanpa membaca teks.',
                'scoring_formula' => 'average',
                'is_published' => true,
            ],
        ];

        foreach ($competitions as $data) {
            Competition::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['event_id' => $event?->id])
            );
        }
    }
}
