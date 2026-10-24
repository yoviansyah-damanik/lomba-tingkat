<?php

namespace App\Livewire\Admin;

use App\Models\RegistrationInvitation;
use App\Models\ScoutGroup;
use App\Services\ScoutGroupRegistrationService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar gugus depan yang diizinkan mendaftar beserta kode undangan dan saklar On/Off
 */
#[Lazy]
class RegistrationInvitationsList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all'; // all, available, used, off

    public bool $showModal = false;

    public string $group_number = '';

    public string $school_name = '';

    public string $kwarran_name = '';

    /** Impor massal: satu baris per gudep, format "nomor gudep; nama sekolah; kwarran" */
    public bool $bulkMode = false;

    public string $bulkText = '';

    public function placeholder(): string
    {
        return '<div class="h-64 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => RegistrationInvitation::count(),
            'available' => RegistrationInvitation::usable()->count(),
            'used' => RegistrationInvitation::whereNotNull('used_at')->count(),
            'off' => RegistrationInvitation::where('is_active', false)->whereNull('used_at')->count(),
        ];
    }

    #[Computed]
    public function invitations()
    {
        return RegistrationInvitation::with('scoutGroup:id,school_name')
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('school_name', 'like', $term)
                    ->orWhere('group_number', 'like', $term)
                    ->orWhere('code', 'like', strtoupper(str_replace('-', '', $this->search)).'%'));
            })
            ->when($this->statusFilter === 'available', fn ($q) => $q->usable())
            ->when($this->statusFilter === 'used', fn ($q) => $q->whereNotNull('used_at'))
            ->when($this->statusFilter === 'off', fn ($q) => $q->where('is_active', false)->whereNull('used_at'))
            ->latest()
            ->paginate(10);
    }

    #[On('open-invitation-form')]
    public function openForm(): void
    {
        $this->reset(['group_number', 'school_name', 'kwarran_name', 'bulkText', 'bulkMode']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->bulkMode ? $this->saveBulk() : $this->saveSingle();
    }

    private function saveSingle(): void
    {
        $validated = $this->validate([
            'group_number' => ['required', 'string', 'max:50', 'regex:/^\d{2}[-.]?\d{1,4}$/'],
            'school_name' => ['required', 'string', 'max:255'],
            'kwarran_name' => ['required', 'in:'.implode(',', ScoutGroupRegistrationService::KWARRANS)],
        ], [
            'group_number.required' => 'Nomor gugus depan wajib diisi.',
            'group_number.regex' => 'Format nomor gugus depan tidak valid. Contoh: 01-093.',
            'school_name.required' => 'Nama sekolah wajib diisi.',
            'kwarran_name.required' => 'Pilih Kwartir Ranting.',
            'kwarran_name.in' => 'Kwartir Ranting tidak valid.',
        ]);

        if ($message = $this->duplicateMessage($validated['group_number'])) {
            $this->addError('group_number', $message);

            return;
        }

        $invitation = $this->createInvitation($validated['group_number'], $validated['school_name'], $validated['kwarran_name']);

        $this->showModal = false;
        $this->resetPage();
        Flux::toast(variant: 'success', heading: 'Berhasil', text: "Kode undangan {$invitation->formattedCode()} dibuat untuk {$invitation->school_name}.");
    }

    private function saveBulk(): void
    {
        $this->validate(['bulkText' => ['required', 'string']], ['bulkText.required' => 'Isi minimal satu baris gugus depan.']);

        $created = 0;
        $errors = [];
        $seen = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($this->bulkText)) as $index => $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = $index + 1;
            $parts = array_map('trim', explode(';', $line));

            if (count($parts) < 3 || ! preg_match('/^\d{2}[-.]?\d{1,4}$/', $parts[0]) || $parts[1] === '') {
                $errors[] = "Baris {$row}: format harus \"nomor gudep; nama sekolah; kwarran\".";

                continue;
            }

            $kwarran = $this->matchKwarran($parts[2]);

            if (! $kwarran) {
                $errors[] = "Baris {$row}: kwarran \"{$parts[2]}\" tidak dikenali.";

                continue;
            }

            $male = ScoutGroup::formatGroupNumbers($parts[0])['male'];

            if (isset($seen[$male]) || $this->duplicateMessage($parts[0])) {
                $errors[] = "Baris {$row}: nomor gudep {$male} sudah terdaftar atau ganda.";

                continue;
            }

            $seen[$male] = true;
            $this->createInvitation($parts[0], $parts[1], $kwarran);
            $created++;
        }

        if ($errors) {
            $this->addError('bulkText', implode("\n", $errors));
        }

        if ($created > 0) {
            $this->resetPage();
            Flux::toast(variant: 'success', heading: 'Berhasil', text: "{$created} kode undangan berhasil dibuat.");

            if (! $errors) {
                $this->showModal = false;
            }
        }
    }

    /**
     * Cocokkan teks bebas (mis. "Utara") ke nama Kwarran resmi
     */
    private function matchKwarran(string $input): ?string
    {
        foreach (ScoutGroupRegistrationService::KWARRANS as $kwarran) {
            if (strcasecmp($kwarran, $input) === 0 || str_ends_with(strtolower($kwarran), strtolower($input))) {
                return $kwarran;
            }
        }

        return null;
    }

    private function createInvitation(string $groupNumber, string $schoolName, string $kwarran): RegistrationInvitation
    {
        return RegistrationInvitation::create([
            'group_number' => ScoutGroup::formatGroupNumbers($groupNumber)['combined'],
            'school_name' => $schoolName,
            'kwarran_name' => $kwarran,
            'created_by' => auth()->user()->recordableId(),
        ]);
    }

    /**
     * Nomor gudep tidak boleh ganda terhadap undangan maupun gugus depan yang sudah terdaftar
     */
    private function duplicateMessage(string $groupNumber): ?string
    {
        $numbers = ScoutGroup::formatGroupNumbers($groupNumber);

        $exists = RegistrationInvitation::where('group_number', $numbers['combined'])->exists()
            || ScoutGroup::where('male_group_number', $numbers['male'])->exists();

        return $exists ? "Nomor gudep {$numbers['male']} sudah memiliki kode undangan atau sudah terdaftar." : null;
    }

    public function toggleActive(string $id): void
    {
        $invitation = RegistrationInvitation::findOrFail($id);

        if ($invitation->used_at) {
            Flux::toast(variant: 'warning', heading: 'Tidak Dapat Diubah', text: 'Kode yang sudah terpakai tidak dapat diaktifkan kembali.');

            return;
        }

        $invitation->update(['is_active' => ! $invitation->is_active]);

        Flux::toast(
            variant: 'info',
            heading: 'Status Diperbarui',
            text: "Kode {$invitation->formattedCode()} ".($invitation->is_active ? 'diaktifkan (On).' : 'dimatikan (Off).'),
        );
    }

    public function delete(string $id): void
    {
        $invitation = RegistrationInvitation::findOrFail($id);

        if ($invitation->used_at) {
            Flux::toast(variant: 'warning', heading: 'Tidak Dapat Dihapus', text: 'Kode yang sudah terpakai disimpan sebagai riwayat registrasi.');

            return;
        }

        $invitation->delete();
        $this->resetPage();

        Flux::toast(variant: 'danger', heading: 'Dihapus', text: "Undangan {$invitation->school_name} dihapus.");
    }

    public function render()
    {
        return view('livewire.admin.registration-invitations-list', [
            'kwarrans' => ScoutGroupRegistrationService::KWARRANS,
        ]);
    }
}
