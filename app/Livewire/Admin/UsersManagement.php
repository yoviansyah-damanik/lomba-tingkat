<?php

namespace App\Livewire\Admin;

use App\Enums\RoleEnum;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Manajemen Pengguna & Tata Kelola Peran (Phase 11)
 */
#[Layout('layouts.app')]
#[Title('Tata Kelola Pengguna & Peran — Administrator')]
class UsersManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    // State Modal & Form
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showResetPasswordModal = false;
    public bool $showDeleteModal = false;

    public ?string $selectedUserId = null;

    // Form Properties
    public string $name = '';
    public string $username = '';
    public string $phone_number = '';
    public string $institution = '';
    public string $position = '';
    public string $password = '';
    public array $selectedRoles = [];

    // Reset Password Property
    public string $newPassword = 'password123';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:100',
            'username' => User::usernameRules($this->selectedUserId),
            'phone_number' => 'nullable|string|max:20',
            'institution' => 'nullable|string|max:150',
            'position' => 'nullable|string|max:100',
            'password' => $this->selectedUserId ? 'nullable|min:6' : 'required|min:6',
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'string|exists:roles,name',
        ];
    }

    protected array $messages = [
        'name.required' => 'Nama pengguna wajib diisi.',
        'username.required' => 'Username wajib diisi.',
        'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan garis bawah.',
        'username.min' => 'Username minimal 3 karakter.',
        'username.unique' => 'Username ini telah digunakan pengguna lain.',
        'password.required' => 'Kata sandi wajib diisi.',
        'password.min' => 'Kata sandi minimal 6 karakter.',
        'selectedRoles.required' => 'Pilih minimal satu peran untuk pengguna ini.',
        'selectedRoles.min' => 'Pilih minimal satu peran untuk pengguna ini.',
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
     * Peran akun yang dikelola otomatis dari data gudep (hanya dapat direset kata sandinya)
     */
    public const MANAGED_ROLES = ['scout_group', 'participant', 'chaperone'];

    /**
     * Peran yang dapat ditetapkan manual pada akun aparatur/pengelola
     */
    #[Computed]
    public function availableRoles(): Collection
    {
        return Role::whereNotIn('name', self::MANAGED_ROLES)->orderBy('name')->get();
    }

    /**
     * Tolak perubahan data selain kata sandi untuk akun Peserta/Gudep/Bindamping
     */
    protected function ensureEditable(User $user): bool
    {
        if (! $user->isManagedAccount() && ! $user->isScoutGroup()) {
            return true;
        }

        Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Akun Peserta, Gudep, dan Bindamping mengikuti data gudep dan hanya dapat diubah kata sandinya.');

        return false;
    }

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        $query = User::with('roles')->latest();

        if (! empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('username', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhere('institution', 'like', $term)
                    ->orWhere('id_number', 'like', $term);
            });
        }

        if (! empty($this->roleFilter)) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->roleFilter));
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate(15);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->selectedUserId = null;
        $this->name = '';
        $this->username = '';
        $this->phone_number = '';
        $this->institution = '';
        $this->position = '';
        $this->password = 'password123';
        $this->selectedRoles = [RoleEnum::Committee->value];

        $this->showCreateModal = true;
    }

    public function createUser(): void
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'phone_number' => $validated['phone_number'] ?: null,
            'institution' => $validated['institution'] ?: null,
            'position' => $validated['position'] ?: null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        foreach ($this->selectedRoles as $roleName) {
            $user->assignRole($roleName);
        }

        AuditLog::log(
            action: 'user_created',
            modelType: User::class,
            modelId: $user->id,
            oldValues: null,
            newValues: [
                'name' => $user->name,
                'username' => $user->username,
                'roles' => $this->selectedRoles,
            ],
            reason: 'Pembuatan akun baru oleh Administrator'
        );

        $this->showCreateModal = false;
        session()->flash('success', "Pengguna {$user->name} berhasil ditambahkan.");
        Flux::toast(variant: 'success', heading: 'Pengguna Ditambahkan', text: "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function openEditModal(string $userId): void
    {
        $this->resetValidation();
        $user = User::with('roles')->findOrFail($userId);

        if (! $this->ensureEditable($user)) {
            return;
        }

        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->phone_number = $user->phone_number ?? '';
        $this->institution = $user->institution ?? '';
        $this->position = $user->position ?? '';
        $this->password = '';
        $this->selectedRoles = $user->roles->pluck('name')->toArray();

        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $validated = $this->validate();

        $user = User::findOrFail($this->selectedUserId);

        if (! $this->ensureEditable($user)) {
            return;
        }

        $oldRoles = $user->roles->pluck('name')->toArray();

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'phone_number' => $validated['phone_number'] ?: null,
            'institution' => $validated['institution'] ?: null,
            'position' => $validated['position'] ?: null,
            ...(! empty($validated['password']) ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        // Sinkronisasi Peran
        $roleIds = Role::whereIn('name', $this->selectedRoles)->pluck('id');
        $user->roles()->sync($roleIds);

        AuditLog::log(
            action: 'user_updated',
            modelType: User::class,
            modelId: $user->id,
            oldValues: [
                'name' => $user->getOriginal('name'),
                'roles' => $oldRoles,
            ],
            newValues: [
                'name' => $user->name,
                'roles' => $this->selectedRoles,
            ],
            reason: 'Pembaruan data akun pengguna oleh Administrator'
        );

        $this->showEditModal = false;
        session()->flash('success', "Data pengguna {$user->name} berhasil diperbarui.");
        Flux::toast(variant: 'success', heading: 'Pengguna Diperbarui', text: "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function toggleActive(string $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak dapat menonaktifkan akun sendiri.');
            return;
        }

        $user = User::findOrFail($userId);

        if (! $this->ensureEditable($user)) {
            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLog::log(
            action: 'user_status_toggled',
            modelType: User::class,
            modelId: $user->id,
            oldValues: ['is_active' => ! $user->is_active],
            newValues: ['is_active' => $user->is_active],
            reason: 'Pengubahan status aktif akun pengguna'
        );

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Akun {$user->name} berhasil {$statusText}.");
        Flux::toast(variant: 'info', heading: 'Status Diperbarui', text: "Akun {$user->name} berhasil {$statusText}.");
    }

    public function openResetPasswordModal(string $userId): void
    {
        $user = User::findOrFail($userId);
        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->newPassword = $this->defaultPasswordFor($user);
        $this->showResetPasswordModal = true;
    }

    /**
     * Kata sandi bawaan reset: tanggal lahir (Ymd) untuk Peserta/Bindamping, selain itu password123
     */
    protected function defaultPasswordFor(User $user): string
    {
        $birthDate = $user->isParticipant()
            ? $user->participant?->birth_date
            : ($user->isChaperone() ? $user->chaperone?->birth_date : null);

        return $birthDate ? $birthDate->format('Ymd') : 'password123';
    }

    public function resetUserPassword(): void
    {
        $this->validate([
            'newPassword' => 'required|min:6',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $user->password = Hash::make($this->newPassword);
        $user->save();

        AuditLog::log(
            action: 'user_password_reset',
            modelType: User::class,
            modelId: $user->id,
            oldValues: null,
            newValues: null,
            reason: 'Reset kata sandi oleh Administrator'
        );

        $this->showResetPasswordModal = false;
        session()->flash('success', "Kata sandi untuk {$user->name} berhasil disetel ulang menjadi '{$this->newPassword}'.");
        Flux::toast(variant: 'success', heading: 'Kata Sandi Direset', text: "Kata sandi untuk {$user->name} berhasil disetel ulang menjadi '{$this->newPassword}'.");
    }

    public function openDeleteModal(string $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun sendiri.');
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak dapat menghapus akun sendiri.');
            return;
        }

        $user = User::findOrFail($userId);

        if (! $this->ensureEditable($user)) {
            return;
        }

        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if ($this->selectedUserId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun sendiri.');
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak dapat menghapus akun sendiri.');
            return;
        }

        $user = User::findOrFail($this->selectedUserId);

        if (! $this->ensureEditable($user)) {
            return;
        }

        $userName = $user->name;

        AuditLog::log(
            action: 'user_deleted',
            modelType: User::class,
            modelId: $user->id,
            oldValues: ['username' => $user->username, 'name' => $user->name],
            newValues: null,
            reason: 'Penghapusan akun oleh Administrator'
        );

        $user->roles()->detach();
        $user->delete();

        $this->showDeleteModal = false;
        session()->flash('success', "Pengguna {$userName} berhasil dihapus dari sistem.");
        Flux::toast(variant: 'danger', heading: 'Pengguna Dihapus', text: "Pengguna {$userName} berhasil dihapus dari sistem.");
    }

    public function render()
    {
        return view('livewire.admin.users-management', [
            'users' => $this->users,
            'availableRoles' => $this->availableRoles,
        ]);
    }
}
