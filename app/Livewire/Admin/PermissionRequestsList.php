<?php

namespace App\Livewire\Admin;

use App\Enums\PermissionTypeEnum;
use App\Models\PermissionRequest;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Riwayat permohonan izin beserta status konfirmasi kembali
 */
#[Lazy]
class PermissionRequestsList extends Component
{
    use WithPagination;

    public string $typeFilter = 'all';

    public string $statusFilter = 'all'; // all, active, completed

    public function placeholder(): string
    {
        return '<div class="h-64 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[On('permission-updated')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $requests = PermissionRequest::with(['scoutGroup', 'members.participant', 'members.chaperone'])
            ->when($this->typeFilter !== 'all', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.permission-requests-list', [
            'requests' => $requests,
            'types' => PermissionTypeEnum::cases(),
        ]);
    }
}
