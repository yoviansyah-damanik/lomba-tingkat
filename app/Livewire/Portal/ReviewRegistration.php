<?php

namespace App\Livewire\Portal;

use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Komponen Livewire 4 Pemeriksaan Ulang & Pengajuan Akhir Pendaftaran Kontingen
 */
#[Layout('layouts.app', ['title' => 'Pemeriksaan Ulang Pendaftaran Kontingen'])]
#[Title('Pemeriksaan Ulang Berkas — LT III')]
class ReviewRegistration extends Component
{
    use WithFileUploads;

    public bool $showIncompleteModal = false;
    public bool $showSubmitModal = false;
    public bool $agreed = false;

    // Berkas pangkalan: Surat Tugas/Mandat dan Bukti Bayar
    public $mandate_letter = null;
    public $payment_proof = null;

    #[Computed]
    public function scoutGroup(): ScoutGroup
    {
        return ScoutGroup::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'registration_code' => 'GDP-'.strtoupper(substr(md5((string) auth()->id()), 0, 6)),
                'school_name' => auth()->user()->name,
                'group_number' => 'Belum Diisi',
                'address' => 'Kota Padangsidimpuan',
                'contact_person' => auth()->user()->name,
                'contact_phone' => auth()->user()->phone_number ?? '08123456789',
                'verification_status' => RegistrationStatusEnum::Draft,
            ]
        );
    }

    /**
     * Ringkasan tagihan pembayaran pendaftaran berdasarkan jumlah regu pangkalan
     */
    #[Computed]
    public function billingSummary(): array
    {
        $group = $this->scoutGroup;
        $group->loadMissing('patrols');
        $config = Event::activePaymentConfig();
        $patrolCount = $group->patrols->count();
        $feePerPatrol = $config['fee_per_patrol'];
        $totalFee = $patrolCount * $feePerPatrol;

        return array_merge($config, [
            'patrol_count' => $patrolCount,
            'total_fee' => $totalFee,
            'formatted_total_fee' => 'Rp ' . number_format($totalFee, 0, ',', '.'),
            'patrols' => $group->patrols->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->patrol_name,
                'category' => $p->gender_category?->label() ?? 'Regu',
                'fee' => $config['formatted_fee_per_patrol'],
            ])->toArray(),
        ]);
    }

    /**
     * Mengumpulkan seluruh item persyaratan pendaftaran kontingen yang belum dipenuhi
     */
    #[Computed]
    public function missingRequirements(): array
    {
        $group = $this->scoutGroup;
        $group->loadMissing(['patrols.participants', 'chaperones']);
        $missing = [];

        // 1. Profil Pangkalan
        if (empty($group->school_name) || $group->group_number === 'Belum Diisi' || empty($group->group_number)) {
            $missing[] = [
                'category' => 'Profil Pangkalan',
                'title' => 'Identitas Gugus Depan Belum Lengkap',
                'description' => 'Nama Sekolah/Pangkalan dan Nomor Gugus Depan wajib diisi dengan benar.',
                'action_url' => route('scout-group.profile'),
                'action_label' => 'Lengkapi Profil',
                'icon' => 'building-library',
                'badge_color' => 'amber',
            ];
        }

        // 2. Data Regu
        if ($group->patrols->isEmpty()) {
            $missing[] = [
                'category' => 'Regu Peserta',
                'title' => 'Belum Ada Regu Terdaftar',
                'description' => 'Pangkalan wajib membentuk minimal 1 Regu (Putra atau Putri).',
                'action_url' => route('scout-group.patrols'),
                'action_label' => 'Tambah Regu',
                'icon' => 'user-group',
                'badge_color' => 'red',
            ];
        } else {
            // Setiap regu wajib tepat 8 peserta
            $size = Patrol::REQUIRED_MEMBERS;
            $wrongSize = $group->patrols->filter(fn ($p) => $p->participants->count() !== $size);
            if ($wrongSize->isNotEmpty()) {
                $names = $wrongSize->map(fn ($p) => "Regu {$p->patrol_name} ({$p->participants->count()}/{$size})")->join(', ');
                $missing[] = [
                    'category' => 'Regu Peserta',
                    'title' => "Jumlah Anggota Regu Belum {$size} Peserta",
                    'description' => "{$names}. Setiap regu wajib memiliki tepat {$size} peserta.",
                    'action_url' => route('scout-group.patrols'),
                    'action_label' => 'Kelola Anggota Regu',
                    'icon' => 'user-plus',
                    'badge_color' => 'red',
                ];
            }

            // Setiap regu wajib memiliki Pinru dan Wapinru
            foreach ([
                [PatrolRoleEnum::PatrolLeader, 'Pimpinan Regu (Pinru)'],
                [PatrolRoleEnum::AssistantPatrolLeader, 'Wakil Pimpinan Regu (Wapinru)'],
            ] as [$role, $label]) {
                $without = $group->patrols->filter(fn ($p) => $p->participants->where('role_in_patrol', $role)->isEmpty());
                if ($without->isNotEmpty()) {
                    $names = $without->map(fn ($p) => 'Regu '.$p->patrol_name)->join(', ');
                    $missing[] = [
                        'category' => 'Regu Peserta',
                        'title' => "Belum Ada {$label}",
                        'description' => "{$names} belum memiliki {$label}. Tetapkan jabatan pada salah satu peserta regu.",
                        'action_url' => route('scout-group.patrols'),
                        'action_label' => 'Kelola Anggota Regu',
                        'icon' => 'exclamation-triangle',
                        'badge_color' => 'red',
                    ];
                }
            }
        }

        // 3. Pembina Pendamping
        if ($group->patrols->isEmpty()) {
            if ($group->chaperones->isEmpty()) {
                $missing[] = [
                    'category' => 'Pembina Pendamping',
                    'title' => 'Belum Ada Bindamping Terdaftar',
                    'description' => 'Tambahkan regu terlebih dahulu, lalu daftarkan Pembina Pendamping sesuai regu Putra/Putri.',
                    'action_url' => route('scout-group.patrols'),
                    'action_label' => 'Tambah Regu',
                    'icon' => 'identification',
                    'badge_color' => 'red',
                ];
            }
        } else {
            // Bindamping wajib mengikuti regu: regu putra -> bindamping putra, regu putri -> bindamping putri
            foreach (['male' => 'Putra', 'female' => 'Putri'] as $gender => $label) {
                $hasPatrol = $group->patrols->contains(fn ($p) => $gender === 'male' ? $p->isBoys() : $p->isGirls());

                if ($hasPatrol && ! $group->chaperones->contains('gender', $gender)) {
                    $missing[] = [
                        'category' => 'Pembina Pendamping',
                        'title' => "Belum Ada Bindamping {$label}",
                        'description' => "Regu {$label} sudah terdaftar, sehingga Pembina Pendamping {$label} wajib didaftarkan.",
                        'action_url' => route('scout-group.chaperones'),
                        'action_label' => "Daftarkan Bindamping {$label}",
                        'icon' => 'identification',
                        'badge_color' => 'red',
                    ];
                }
            }
        }

        // 4. Surat Tugas / Mandat Gugus Depan
        $hasMandateLetter = ! empty($group->mandate_letter_path) || ! empty($this->mandate_letter);
        if (! $hasMandateLetter) {
            $missing[] = [
                'category' => 'Berkas Pangkalan',
                'title' => 'Surat Tugas / Mandat Belum Diunggah',
                'description' => 'Surat Tugas resmi dari Gugusdepan wajib diunggah pada Bagian 4 Berkas Administrasi.',
                'action_url' => '#section-administrative-docs',
                'action_label' => 'Unggah di Halaman Ini',
                'icon' => 'document-arrow-up',
                'badge_color' => 'amber',
            ];
        }

        // 5. Bukti Pembayaran Pendaftaran
        $hasPaymentProof = ! empty($group->payment_proof_path) || ! empty($this->payment_proof);
        if (! $hasPaymentProof) {
            $billing = $this->billingSummary;
            $feeNote = $billing['patrol_count'] > 0
                ? "Tagihan sebesar {$billing['formatted_total_fee']} untuk {$billing['patrol_count']} regu ({$billing['formatted_fee_per_patrol']}/regu)."
                : "Biaya dihitung {$billing['formatted_fee_per_patrol']} per regu yang didaftarkan.";

            $missing[] = [
                'category' => 'Berkas Pangkalan',
                'title' => 'Bukti Pembayaran Belum Diunggah',
                'description' => "Bukti transfer pembayaran biaya pendaftaran LT III wajib diunggah. {$feeNote}",
                'action_url' => '#section-administrative-docs',
                'action_label' => 'Unggah di Halaman Ini',
                'icon' => 'receipt-percent',
                'badge_color' => 'amber',
            ];
        }

        return $missing;
    }

    /**
     * Persyaratan belum terpenuhi dikelompokkan per bagian halaman: profile, patrols, chaperones, documents
     *
     * @return array<string, array<int, array<string, string>>>
     */
    #[Computed]
    public function sectionWarnings(): array
    {
        $sections = [
            'Profil Pangkalan' => 'profile',
            'Regu Peserta' => 'patrols',
            'Pembina Pendamping' => 'chaperones',
            'Berkas Pangkalan' => 'documents',
        ];

        return collect($this->missingRequirements)
            ->groupBy(fn ($item) => $sections[$item['category']] ?? 'other')
            ->map(fn ($items) => $items->values()->all())
            ->all();
    }

    /**
     * Ringkasan kelengkapan 7 berkas persyaratan peserta
     */
    #[Computed]
    public function participantDocumentsSummary(): array
    {
        $group = $this->scoutGroup;
        $group->loadMissing('patrols.participants');
        $totalParticipants = 0;
        $incompleteParticipantsCount = 0;

        foreach ($group->patrols as $patrol) {
            foreach ($patrol->participants as $participant) {
                $totalParticipants++;
                $hasAllDocs = ! empty($participant->doc_kta_path)
                    && ! empty($participant->doc_bpjs_path)
                    && ! empty($participant->doc_health_certificate_path)
                    && ! empty($participant->doc_birth_certificate_path)
                    && ! empty($participant->doc_family_card_path)
                    && ! empty($participant->doc_sku_path)
                    && ! empty($participant->doc_skk_path);

                if (! $hasAllDocs) {
                    $incompleteParticipantsCount++;
                }
            }
        }

        return [
            'total' => $totalParticipants,
            'incomplete' => $incompleteParticipantsCount,
            'complete' => $totalParticipants - $incompleteParticipantsCount,
        ];
    }

    /**
     * Peringatan berkas persyaratan peserta yang belum diunggah, satu baris per peserta
     *
     * @return array<int, string>
     */
    #[Computed]
    public function participantDocumentWarnings(): array
    {
        $group = $this->scoutGroup;
        $group->loadMissing('patrols.participants');
        $warnings = [];

        foreach ($group->patrols as $patrol) {
            foreach ($patrol->participants as $participant) {
                $missing = collect(Participant::DOCUMENTS)
                    ->filter(fn ($doc) => empty($participant->{$doc[0]}))
                    ->map(fn ($doc) => str_replace('Scan ', '', $doc[1]))
                    ->values();

                if ($missing->isNotEmpty()) {
                    $warnings[] = "{$participant->full_name} (Regu {$patrol->patrol_name}) belum mengunggah: {$missing->join(', ')}.";
                }
            }
        }

        return $warnings;
    }

    #[Computed]
    public function readiness(): array
    {
        $group = $this->scoutGroup;
        $group->loadMissing(['patrols.participants', 'chaperones']);

        $hasPatrol = $group->patrols->isNotEmpty();
        $hasChaperone = $group->chaperones->isNotEmpty();
        $isProfileComplete = ! empty($group->school_name) && $group->group_number !== 'Belum Diisi' && ! empty($group->group_number);
        $hasMandateLetter = ! empty($group->mandate_letter_path) || ! empty($this->mandate_letter);
        $hasPaymentProof = ! empty($group->payment_proof_path) || ! empty($this->payment_proof);

        $isReady = empty($this->missingRequirements);

        return [
            'is_ready' => $isReady,
            'has_patrol' => $hasPatrol,
            'has_chaperone' => $hasChaperone,
            'is_profile_complete' => $isProfileComplete,
            'has_mandate_letter' => $hasMandateLetter,
            'has_payment_proof' => $hasPaymentProof,
            'missing_count' => count($this->missingRequirements),
        ];
    }

    /**
     * Memeriksa kelengkapan persyaratan saat tombol Ajukan diklik
     */
    public function attemptSubmit(): void
    {
        if (! empty($this->missingRequirements)) {
            $this->showIncompleteModal = true;
            return;
        }

        $this->showSubmitModal = true;
    }

    public function openSubmitModal(): void
    {
        $this->attemptSubmit();
    }

    /**
     * Proses pengajuan akhir pendaftaran kontingen
     */
    public function submitRegistration(): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            Flux::toast('Pendaftaran sudah diajukan atau terkunci.', variant: 'danger');
            return;
        }

        if (! empty($this->missingRequirements)) {
            $this->showIncompleteModal = true;
            Flux::toast('Mohon lengkapi seluruh persyaratan pendaftaran terlebih dahulu.', variant: 'warning');
            return;
        }

        $this->validate([
            'mandate_letter' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'mandate_letter.file' => 'Berkas Surat Tugas / Mandat harus valid.',
            'mandate_letter.mimes' => 'Surat Tugas / Mandat harus berformat PDF.',
            'mandate_letter.max' => 'Ukuran Surat Tugas / Mandat maksimal 5MB.',
            'payment_proof.file' => 'Berkas Bukti Bayar harus valid.',
            'payment_proof.mimes' => 'Bukti Bayar harus berformat PDF, JPG, atau PNG.',
            'payment_proof.max' => 'Ukuran Bukti Bayar maksimal 5MB.',
        ]);

        $groupUpdates = [
            'verification_status' => RegistrationStatusEnum::Submitted,
            'submitted_at' => now(),
        ];

        if ($this->mandate_letter) {
            if ($group->mandate_letter_path && Storage::disk('public')->exists($group->mandate_letter_path)) {
                Storage::disk('public')->delete($group->mandate_letter_path);
            }
            $groupUpdates['mandate_letter_path'] = $this->mandate_letter->store('scout-groups/documents', 'public');
        }

        if ($this->payment_proof) {
            if ($group->payment_proof_path && Storage::disk('public')->exists($group->payment_proof_path)) {
                Storage::disk('public')->delete($group->payment_proof_path);
            }
            $groupUpdates['payment_proof_path'] = $this->payment_proof->store('scout-groups/documents', 'public');
        }

        $group->update($groupUpdates);

        $group->patrols()->update(['status' => RegistrationStatusEnum::Submitted]);
        Participant::whereIn('patrol_id', $group->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Submitted]);
        $group->chaperones()->update(['status' => RegistrationStatusEnum::Submitted]);

        $this->showSubmitModal = false;
        $this->showIncompleteModal = false;
        session()->flash('success', 'Pendaftaran kontingen berhasil diajukan untuk verifikasi Panitia Kwarcab.');
        Flux::toast('Pendaftaran kontingen berhasil diajukan.', variant: 'success');

        $this->redirectRoute('scout-group.index');
    }

    public function render()
    {
        $scoutGroup = $this->scoutGroup;
        $scoutGroup->load(['patrols.participants', 'chaperones']);

        return view('livewire.portal.review-registration', [
            'scoutGroup' => $scoutGroup,
            'readiness' => $this->readiness,
            'missingRequirements' => $this->missingRequirements,
            'participantDocs' => $this->participantDocumentsSummary,
            'billingSummary' => $this->billingSummary,
        ]);
    }
}
