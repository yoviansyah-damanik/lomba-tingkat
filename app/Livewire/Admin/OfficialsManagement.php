<?php

namespace App\Livewire\Admin;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Pengelolaan Aparatur Panitia & Dewan Juri
 */
#[Layout('layouts.app')]
#[Title('Aparatur Panitia & Dewan Juri — Livewire 4')]
class OfficialsManagement extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $roleFilter = 'all'; // all, committee, judge, dual
    public string $statusFilter = 'all'; // all, active, inactive

    // State Modal Form Tambah / Edit
    public bool $showModal = false;
    public ?string $editingId = null;

    // Field Form
    public string $name = '';
    public string $idNumber = '';
    public string $phone_number = '';
    public string $scout_id = '';
    public string $institution = 'Kwarcab Kota Padangsidimpuan';
    public string $position = '';
    public string $committee_position = '';
    public array $selectedRoles = ['committee'];
    public bool $is_administrator = false;
    public bool $is_active = true;
    public $avatar = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Hitung statistik aparatur secara reaktif
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'total' => User::whereHas('roles', fn ($q) => $q->whereIn('name', [RoleEnum::Committee->value, RoleEnum::Judge->value]))->count(),
            'committee' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))->count(),
            'judge' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))->count(),
            'dual' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))
                          ->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))->count(),
        ];
    }

    /**
     * Ambil data aparatur dengan filter dan pencarian
     */
    public function getOfficialsProperty()
    {
        $query = User::with('roles')->whereHas('roles', function ($q) {
            $q->whereIn('name', [RoleEnum::Committee->value, RoleEnum::Judge->value]);
        });

        if ($this->roleFilter === 'committee') {
            $query->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value));
        } elseif ($this->roleFilter === 'judge') {
            $query->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value));
        } elseif ($this->roleFilter === 'dual') {
            $query->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))
                  ->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value));
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if (! empty($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('id_number', 'like', "%{$term}%")
                  ->orWhere('institution', 'like', "%{$term}%")
                  ->orWhere('position', 'like', "%{$term}%")
                  ->orWhere('scout_id', 'like', "%{$term}%");
            });
        }

        return $query->latest()->paginate(12);
    }

    /**
     * Buka modal untuk membuat aparatur baru
     */
    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    /**
     * Buka modal untuk mengedit aparatur
     */
    public function openEditModal(string $id): void
    {
        $this->resetValidation();
        $user = User::with('roles')->findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->idNumber = (string) $user->id_number;
        $this->phone_number = $user->phone_number ?? '';
        $this->scout_id = $user->scout_id ?? '';
        $this->institution = $user->institution ?? 'Kwarcab Kota Padangsidimpuan';
        $this->position = $user->position ?? '';
        $this->committee_position = $user->committee_position ?? '';
        $this->selectedRoles = $user->roles->pluck('name')->intersect(['committee', 'judge'])->toArray();
        $this->is_administrator = $user->isAdministrator();
        $this->is_active = (bool) $user->is_active;
        $this->avatar = null;

        $this->showModal = true;
    }

    /**
     * Simpan data aparatur (Create / Update)
     */
    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'scout_id' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:100'],
            'committee_position' => ['nullable', 'string', 'max:100'],
            'selectedRoles' =>['required', 'array', 'min:1'],
            'selectedRoles.*' => ['in:committee,judge'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];

        $validated = $this->validate($rules);

        // Penunjukan Administrator hanya oleh Superadmin dan hanya untuk akun Panitia
        $canDesignate = auth()->user()->isSuperadmin();
        $designateAdmin = $canDesignate && $this->is_administrator;
        if ($designateAdmin && ! in_array('committee', $validated['selectedRoles'], true)) {
            $this->addError('is_administrator', 'Administrator harus dipilih dari akun Panitia.');

            return;
        }

        if ($this->editingId) {
            $official = User::findOrFail($this->editingId);
            $data = [
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'] ?: null,
                'scout_id' => $validated['scout_id'] ?: null,
                'institution' => $validated['institution'] ?: 'Kwarcab Kota Padangsidimpuan',
                'position' => $validated['position'] ?: null,
                'committee_position' => $this->committeePositionFor($validated),
            ];

            if ($this->avatar) {
                if ($official->avatar_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($official->avatar_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($official->avatar_path);
                }
                $data['avatar_path'] = $this->avatar->store('avatars', 'public');
            }

            $official->update($data);

            // Peran akun otomatis menyesuaikan dengan peran yang dipilih
            $nonOfficialRoleIds = $official->roles()->whereNotIn('name', ['committee', 'judge'])->pluck('roles.id')->toArray();
            $newRoleIds = Role::whereIn('name', $validated['selectedRoles'])->pluck('id')->toArray();
            $official->roles()->sync(array_merge($nonOfficialRoleIds, $newRoleIds));

            if ($canDesignate) {
                $designateAdmin ? $official->assignRole(RoleEnum::Admin) : $official->removeRole(RoleEnum::Admin);
            } elseif (! in_array('committee', $validated['selectedRoles'], true)) {
                $official->removeRole(RoleEnum::Admin);
            }

            Flux::toast(variant: 'success', heading: 'Berhasil', text: "Data aparatur {$official->name} berhasil diperbarui.");
        } else {
            // ID unik aparatur: A-{6 digit acak}
            $officialIdNumber = User::generateOfficialIdNumber();

            $data = [
                'name' => $validated['name'],
                'id_number' => $officialIdNumber,
                'username' => $officialIdNumber,
                'password' => Hash::make($officialIdNumber), // Password default mengikuti ID
                'must_change_password' => true,             // Login pertama diminta ganti password
                'phone_number' => $validated['phone_number'] ?: null,
                'scout_id' => $validated['scout_id'] ?: null,
                'institution' => $validated['institution'] ?: 'Kwarcab Kota Padangsidimpuan',
                'position' => $validated['position'] ?: null,
                'committee_position' => $this->committeePositionFor($validated),
                'is_active' => true,                       // Akun otomatis dibuat dan aktif
                'qr_token' => (string) Str::uuid(),
                'qr_generated_at' => now(),
            ];

            if ($this->avatar) {
                $data['avatar_path'] = $this->avatar->store('avatars', 'public');
            }

            $official = User::create($data);

            foreach ($validated['selectedRoles'] as $roleName) {
                $official->assignRole($roleName);
            }

            if ($designateAdmin) {
                $official->assignRole(RoleEnum::Admin);
            }

            Flux::toast(variant: 'success', heading: 'Berhasil', text: "Aparatur {$official->name} berhasil didaftarkan dengan ID {$official->id_number}.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Beralih status aktif / non-aktif aparatur secara instan
     */
    public function toggleStatus(string $id): void
    {
        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        Flux::toast(variant: 'info', heading: 'Status Diperbarui', text: "Aparatur {$user->name} {$statusText}.");
    }

    /**
     * Hapus aparatur beserta akunnya
     */
    public function deleteOfficial(string $id): void
    {
        $user = User::findOrFail($id);
        $name = $user->name;

        if ($user->avatar_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar_path);
        }

        $user->roles()->detach();
        $user->delete();

        Flux::toast(variant: 'danger', heading: 'Dihapus', text: "Aparatur {$name} dan akunnya berhasil dihapus.");
    }

    public function delete(string $id): void
    {
        $this->deleteOfficial($id);
    }

    /**
     * Reset form data
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->idNumber = '';
        $this->phone_number = '';
        $this->scout_id = '';
        $this->institution = 'Kwarcab Kota Padangsidimpuan';
        $this->position = '';
        $this->committee_position = '';
        $this->selectedRoles = ['committee'];
        $this->is_administrator = false;
        $this->is_active = true;
        $this->avatar = null;
    }

    /**
     * Posisi kepanitiaan hanya disimpan untuk akun berperan Panitia
     */
    private function committeePositionFor(array $validated): ?string
    {
        return in_array('committee', $validated['selectedRoles'], true) ? ($validated['committee_position'] ?: null) : null;
    }

    public function render()
    {
        return view('livewire.admin.officials-management', [
            'officials' => $this->officials,
            'stats' => $this->stats,
        ]);
    }
}
