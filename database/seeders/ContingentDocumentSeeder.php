<?php

namespace Database\Seeders;

use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\ScoutGroup;
use Database\Seeders\Support\PlaceholderFiles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Melengkapi data pendaftaran gugus depan contoh: unggahan pangkalan (surat tugas, bukti bayar), serta biodata
 * lengkap dan unggahan seluruh peserta dan bindamping. Format sesuai ketentuan: pas foto = gambar, bukti transfer =
 * PDF atau gambar (bergantian), surat tugas dan seluruh scan dokumen peserta = PDF.
 *
 * Aman dijalankan ulang: mengisi kolom/berkas yang kosong dan mengganti berkas lama yang formatnya tidak sesuai
 * (surat tugas/scan dokumen non-PDF). Berkas contoh ditulis ke disk public.
 */
class ContingentDocumentSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('scout-groups/documents');

        $groups = ScoutGroup::with(['patrols.participants', 'chaperones'])->orderBy('registration_code')->get();

        foreach ($groups as $index => $group) {
            $this->seedGroupUploads($group, $index);

            foreach ($group->patrols as $patrol) {
                foreach ($patrol->participants as $m => $participant) {
                    $this->completeParticipant($participant, $group, $index + $m);
                }
            }

            foreach ($group->chaperones as $chaperone) {
                $this->completeChaperone($chaperone, $group, $index);
            }
        }

        $this->command?->info('Data lengkap & unggahan '.$groups->count().' gugus depan terisi.');
    }

    private function seedGroupUploads(ScoutGroup $group, int $index): void
    {
        $updates = [];

        if ($this->needsPdf($group->mandate_letter_path)) {
            $updates['mandate_letter_path'] = PlaceholderFiles::pdf('scout-groups/documents', 'Surat Tugas / Mandat Gugus Depan', $group->school_name, 'mandat');
        }

        // Bukti transfer boleh PDF atau gambar: gudep bergantian agar kedua format tampil
        if (! $group->payment_proof_path) {
            $updates['payment_proof_path'] = $index % 2 === 0
                ? PlaceholderFiles::scan('scout-groups/documents', 'Bukti Pembayaran Pendaftaran', $group->school_name, 'bukti_bayar')
                : PlaceholderFiles::pdf('scout-groups/documents', 'Bukti Pembayaran Pendaftaran', $group->school_name, 'bukti_bayar');
        }

        if ($updates) {
            $group->updateQuietly($updates);
        }
    }

    /**
     * Berkas wajib PDF: kosong atau masih berformat lama (bukan PDF); berkas lama dihapus dari disk
     */
    private function needsPdf(?string $path): bool
    {
        if (! $path) {
            return true;
        }

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
            return false;
        }

        Storage::disk('public')->delete($path);

        return true;
    }

    private function completeParticipant(Participant $participant, ScoutGroup $group, int $seed): void
    {
        $updates = [];
        $directory = 'participants';

        if (! $participant->photo_path) {
            $updates['photo_path'] = PlaceholderFiles::photo($directory, $participant->full_name, $seed);
        }

        foreach (Participant::DOCUMENTS as [$column, $label]) {
            if ($this->needsPdf($participant->{$column})) {
                $updates[$column] = PlaceholderFiles::pdf($directory, $label, $participant->full_name, 'scan');
            }
        }

        $fill = fn (string $column, mixed $value) => $participant->{$column} ?: ($updates[$column] = $value);

        $fill('home_phone', '0634'.random_int(100000, 999999));
        $fill('parent_address', $participant->home_address ?: 'Jl. Merdeka, Padangsidimpuan');
        $fill('parent_city', 'Padangsidimpuan');
        $fill('parent_postal_code', '22715');
        $fill('parent_home_phone', '0634'.random_int(100000, 999999));

        if (! $participant->formal_education) {
            $updates['formal_education'] = [
                ['level' => 'SD', 'school_name' => 'SD Negeri '.random_int(1, 20).' Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => ''],
                ['level' => 'SMP', 'school_name' => $group->school_name, 'city' => 'Padangsidimpuan', 'graduation_year' => '', 'notes' => 'Masih bersekolah'],
            ];
        }

        if (! $participant->scout_siaga) {
            $updates['scout_siaga'] = array_replace_recursive(Participant::defaultScoutSiaga(), [
                'year' => '2020',
                'group_number' => $group->male_group_number ?: $group->group_number,
                'pangkalan' => $group->school_name,
                'levels' => ['mula' => '2020', 'bantu' => '2021', 'tata' => '2022'],
                'activities' => ['Persami', 'Pesta Siaga', 'Bakti Lingkungan', '', '', ''],
            ]);
        }

        if (! $participant->scout_penggalang) {
            $updates['scout_penggalang'] = array_replace_recursive(Participant::defaultScoutPenggalang(), [
                'year' => '2024',
                'group_number' => $group->male_group_number ?: $group->group_number,
                'pangkalan' => $group->school_name,
                'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '2026'],
                'activities' => ['Perkemahan Sabtu Minggu', 'Lomba Tingkat II', 'Bakti Masyarakat', '', '', '', ''],
                'tkk_purwa' => ['Pertolongan Pertama', 'Pioneering', 'Tali Temali', '', '', '', '', '', '', ''],
            ]);
        }

        if ($updates) {
            $participant->updateQuietly($updates);
        }
    }

    private function completeChaperone(Chaperone $chaperone, ScoutGroup $group, int $seed): void
    {
        $updates = [];

        if (! $chaperone->photo_path) {
            $updates['photo_path'] = PlaceholderFiles::photo('chaperones', $chaperone->full_name, $seed + 3);
        }

        $fill = fn (string $column, mixed $value) => $chaperone->{$column} ?: ($updates[$column] = $value);

        $fill('blood_type', ['A', 'B', 'AB', 'O'][$seed % 4]);
        $fill('home_address', 'Jl. Imam Bonjol No. '.(($seed % 40) + 1).', Padangsidimpuan');
        $fill('city', 'Padangsidimpuan');
        $fill('postal_code', '22715');
        $fill('home_phone', '0634'.random_int(100000, 999999));

        if (! $chaperone->formal_education) {
            $updates['formal_education'] = [
                ['level' => 'SD', 'school_name' => 'SD Negeri '.random_int(1, 20).' Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '1996', 'notes' => ''],
                ['level' => 'SMP', 'school_name' => 'SMP Negeri '.random_int(1, 9).' Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '1999', 'notes' => ''],
                ['level' => 'SMA', 'school_name' => 'SMA Negeri '.random_int(1, 6).' Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2002', 'notes' => ''],
                ['level' => 'S1', 'school_name' => 'Universitas Negeri Medan', 'city' => 'Medan', 'graduation_year' => '2007', 'notes' => 'S.Pd'],
                ['level' => 'S2', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
            ];
        }

        if (! $chaperone->scout_experiences) {
            $updates['scout_experiences'] = [
                ['level' => 'Siaga', 'year' => '1995', 'group_number' => $group->group_number, 'base' => $group->school_name],
                ['level' => 'Penggalang', 'year' => '1998', 'group_number' => $group->group_number, 'base' => $group->school_name],
                ['level' => 'Penegak', 'year' => '2002', 'group_number' => $group->group_number, 'base' => 'SMA Negeri Padangsidimpuan'],
                ['level' => 'Pandega', 'year' => '2006', 'group_number' => 'Kwarcab Padangsidimpuan', 'base' => 'Dewan Kerja Cabang'],
                ['level' => 'Pembina', 'year' => '2010', 'group_number' => $group->group_number, 'base' => $group->school_name],
            ];
        }

        if (! $chaperone->scout_trainings) {
            $updates['scout_trainings'] = [
                ['course' => 'KMD', 'year' => '2011', 'location' => 'Kwarcab Padangsidimpuan', 'notes' => 'Lulus'],
                ['course' => 'KML', 'year' => '2014', 'location' => 'Kwarda Sumatera Utara', 'notes' => 'Lulus'],
                ['course' => 'KPD', 'year' => '', 'location' => '', 'notes' => ''],
                ['course' => 'KPL', 'year' => '', 'location' => '', 'notes' => ''],
            ];
        }

        if (! $chaperone->other_trainings) {
            $updates['other_trainings'] = [
                ['name' => 'Pelatihan Pembina Penggalang', 'year' => '2023', 'location' => 'Padangsidimpuan', 'notes' => 'Peserta'],
            ];
        }

        if ($updates) {
            $chaperone->updateQuietly($updates);
        }
    }
}
