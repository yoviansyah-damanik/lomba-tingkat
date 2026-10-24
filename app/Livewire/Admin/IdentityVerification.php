<?php

namespace App\Livewire\Admin;

use App\Services\QrCodeService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Verifikasi identitas peserta, bindamping, panitia, dan juri dengan memindai QR Code kartu digital.
 * Hanya untuk Administrator dan Superadmin (dijaga middleware rute).
 */
#[Layout('layouts.app', ['title' => 'Verifikasi Identitas'])]
#[Title('Verifikasi Identitas — LT III')]
class IdentityVerification extends Component
{
    private const HISTORY_LIMIT = 8;

    public string $manualInput = '';

    /** Hasil pemindaian terakhir (tanpa model, agar aman diserialisasi Livewire) */
    public ?array $result = null;

    /** @var array<int, array{name: string, role_label: string, valid: bool, is_verified: bool, scanned_at: string}> */
    public array $history = [];

    public function verifyManual(): void
    {
        $this->verifyToken($this->manualInput);
        $this->manualInput = '';
    }

    /**
     * Selesaikan isi QR (URL verifikasi atau token mentah) menjadi identitas terakreditasi
     */
    public function verifyToken(string $raw): void
    {
        $token = app(QrCodeService::class)->tokenFromScan($raw);
        $resolved = $token !== '' ? app(QrCodeService::class)->resolveToken($token) : null;

        $scannedAt = now()->timezone('Asia/Jakarta')->format('H:i:s');

        if (! $resolved) {
            $this->result = ['valid' => false, 'raw' => Str::limit(trim($raw), 60), 'scanned_at' => $scannedAt];
            $this->remember('QR tidak dikenali', '-', false, false, $scannedAt);

            return;
        }

        $entity = $resolved['entity'];

        $this->result = [
            'valid' => true,
            'type' => $resolved['type'],
            'title' => $resolved['title'],
            'category_label' => $resolved['category_label'],
            'name' => $resolved['name'],
            'role_label' => $resolved['role_label'],
            'id_number' => $resolved['id_number'],
            'school_name' => $resolved['school_name'],
            'kwarran_name' => $resolved['kwarran_name'],
            'patrol_name' => $resolved['patrol_name'],
            'group_number' => $resolved['group_number'],
            'scout_id' => $resolved['scout_id'],
            'blood_type' => $resolved['blood_type'],
            'gender' => $resolved['gender'],
            'birth_info' => $resolved['birth_info'],
            'photo_url' => $resolved['photo_path'] ? asset('storage/'.$resolved['photo_path']) : null,
            'is_verified' => (bool) $resolved['is_verified'],
            'status_label' => $resolved['status_label'],
            'detail_url' => match ($resolved['type']) {
                'participant' => route('admin.participants.show', $entity->id),
                'chaperone' => route('admin.chaperones.show', $entity->id),
                default => null,
            },
            'card_url' => match ($resolved['type']) {
                'participant' => route('digital-cards.participant', $entity->id),
                'chaperone' => route('digital-cards.chaperone', $entity->id),
                default => route('digital-cards.staff', $entity->id),
            },
            'scanned_at' => $scannedAt,
        ];

        $this->remember($resolved['name'], $resolved['role_label'], true, (bool) $resolved['is_verified'], $scannedAt);
    }

    public function clearResult(): void
    {
        $this->result = null;
    }

    private function remember(string $name, string $role, bool $valid, bool $verified, string $scannedAt): void
    {
        array_unshift($this->history, [
            'name' => $name,
            'role_label' => $role,
            'valid' => $valid,
            'is_verified' => $verified,
            'scanned_at' => $scannedAt,
        ]);

        $this->history = array_slice($this->history, 0, self::HISTORY_LIMIT);
    }

    public function render()
    {
        return view('livewire.admin.identity-verification');
    }
}
