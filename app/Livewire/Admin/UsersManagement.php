<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tata Kelola Pengguna & Peran (khusus Superadmin): data pengguna tidak dapat dibuat, diubah, atau dihapus.
 * Superadmin hanya dapat mengaktifkan/menonaktifkan akun dan mengganti kata sandi pengguna.
 */
#[Layout('layouts.app')]
#[Title('Tata Kelola Pengguna & Peran — Superadmin')]
class UsersManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    public bool $showResetPasswordModal = false;

    public ?string $selectedUserId = null;

    /** Pengguna yang riwayat login dan audit lognya sedang dibuka pada modal */
    public ?string $historyUserId = null;

    /** Nama pengguna terpilih (ditampilkan pada modal reset kata sandi) */
    public string $name = '';

    public string $newPassword = 'password123';

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
     * Seluruh peran sebagai pilihan filter
     */
    #[Computed]
    public function availableRoles(): Collection
    {
        return Role::orderBy('name')->get();
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

    public function toggleActive(string $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user = User::findOrFail($userId);

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

    public function openHistoryModal(string $userId): void
    {
        $this->historyUserId = User::findOrFail($userId)->id;
    }

    public function closeHistoryModal(): void
    {
        $this->historyUserId = null;
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
            reason: 'Reset kata sandi oleh Superadmin'
        );

        $this->showResetPasswordModal = false;
        session()->flash('success', "Kata sandi untuk {$user->name} berhasil disetel ulang menjadi '{$this->newPassword}'.");
        Flux::toast(variant: 'success', heading: 'Kata Sandi Direset', text: "Kata sandi untuk {$user->name} berhasil disetel ulang menjadi '{$this->newPassword}'.");
    }

    public function render()
    {
        return view('livewire.admin.users-management', [
            'users' => $this->users,
            'availableRoles' => $this->availableRoles,
        ]);
    }
}
