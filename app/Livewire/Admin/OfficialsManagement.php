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
    public string $password = '';
    public string $phone_number = '';
    public string $scout_id = '';
    public string $institution = 'Kwarcab Kota Padangsidimpuan';
    public string $position = '';
    public array $selectedRoles = ['committee'];
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
        $this->password = '';
        $this->phone_number = $user->phone_number ?? '';
        $this->scout_id = $user->scout_id ?? '';
        $this->institution = $user->institution ?? 'Kwarcab Kota Padangsidimpuan';
        $this->position = $user->position ?? '';
        $this->selectedRoles = $user->roles->pluck('name')->intersect(['committee', 'judge'])->toArray();
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
            'selectedRoles' => ['required', 'array', 'min:1'],
            'selectedRoles.*' => ['in:committee,judge'],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];

        if (! $this->editingId) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $validated = $this->validate($rules);

        $data = [
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'] ?: null,
            'scout_id' => $validated['scout_id'] ?: null,
            'institution' => $validated['institution'] ?: 'Kwarcab Kota Padangsidimpuan',
            'position' => $validated['position'] ?: null,
            'is_active' => $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($this->avatar) {
            $data['avatar_path'] = $this->avatar->store('avatars', 'public');
        }

        if ($this->editingId) {
            $official = User::findOrFail($this->editingId);
            $official->update($data);

            $roleIds = Role::whereIn('name', $validated['selectedRoles'])->pluck('id')->toArray();
            $official->roles()->sync($roleIds);

            Flux::toast(variant: 'success', heading: 'Berhasil', text: "Data aparatur {$official->name} berhasil diperbarui.");
        } else {
            $data['qr_token'] = (string) Str::uuid();
            $data['qr_generated_at'] = now();

            $official = User::create($data);

            foreach ($validated['selectedRoles'] as $roleName) {
                $official->assignRole($roleName);
            }

            Flux::toast(variant: 'success', heading: 'Berhasil', text: "Aparatur {$official->name} berhasil didaftarkan.");
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
     * Hapus aparatur
     */
    public function deleteOfficial(string $id): void
    {
        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        Flux::toast(variant: 'danger', heading: 'Dihapus', text: "Aparatur {$name} berhasil dihapus.");
    }

    /**
     * Reset form data
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->idNumber = '';
        $this->password = '';
        $this->phone_number = '';
        $this->scout_id = '';
        $this->institution = 'Kwarcab Kota Padangsidimpuan';
        $this->position = '';
        $this->selectedRoles = ['committee'];
        $this->is_active = true;
        $this->avatar = null;
    }

    public function render()
    {
        return view('livewire.admin.officials-management', [
            'officials' => $this->officials,
            'stats' => $this->stats,
        ]);
    }
}
