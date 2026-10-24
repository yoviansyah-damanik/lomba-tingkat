<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Isi modal riwayat satu pengguna: riwayat login dan audit log (tindakan pengguna serta tindakan terhadap akunnya)
 */
#[Lazy]
class UserActivityHistory extends Component
{
    private const LIMIT = 25;

    public string $userId;

    public string $tab = 'login'; // login, audit

    public function placeholder(): string
    {
        return '<div class="space-y-3 p-6"><div class="h-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div><div class="h-48 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div></div>';
    }

    #[Computed]
    public function user(): ?User
    {
        return User::with('roles')->find($this->userId);
    }

    #[Computed]
    public function logins(): Collection
    {
        return LoginHistory::where('user_id', $this->userId)->latest('created_at')->limit(self::LIMIT)->get();
    }

    /**
     * Audit log yang dilakukan pengguna ini atau yang menyasar akunnya
     */
    #[Computed]
    public function audits(): Collection
    {
        return AuditLog::with('user')
            ->where(function ($q) {
                $q->where('user_id', $this->userId)
                    ->orWhere(fn ($q2) => $q2->where('model_type', User::class)->where('model_id', $this->userId));
            })
            ->latest('created_at')
            ->limit(self::LIMIT)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.user-activity-history');
    }
}
