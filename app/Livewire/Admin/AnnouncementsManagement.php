<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use App\Models\AuditLog;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar pengumuman resmi kegiatan; penulisan dan penyuntingan ada di halaman AnnouncementForm
 */
#[Layout('layouts.app')]
#[Title('Manajemen Pengumuman Kegiatan — Administrator')]
class AnnouncementsManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';

    public bool $showDeleteModal = false;
    public ?string $selectedAnnouncementId = null;
    public string $title = '';

    /**
     * Tampilkan pesan hasil simpan dari halaman form setelah pengalihan
     */
    public function mount(): void
    {
        if ($message = session('success')) {
            Flux::toast(variant: 'success', heading: 'Berhasil', text: $message);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function announcements(): LengthAwarePaginator
    {
        $query = Announcement::with('author')->latest();

        if (! empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        }

        if (! empty($this->categoryFilter)) {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->statusFilter === 'published') {
            $query->where('is_published', true);
        } elseif ($this->statusFilter === 'draft') {
            $query->where('is_published', false);
        }

        return $query->paginate(10);
    }

    public function togglePublish(string $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->is_published = ! $announcement->is_published;
        $announcement->published_at = $announcement->is_published ? ($announcement->published_at ?? now()) : null;
        $announcement->save();

        AuditLog::log(
            action: 'announcement_status_toggled',
            modelType: Announcement::class,
            modelId: $announcement->id,
            oldValues: ['is_published' => ! $announcement->is_published],
            newValues: ['is_published' => $announcement->is_published],
            reason: 'Pengubahan status publikasi pengumuman'
        );

        $status = $announcement->is_published ? 'dipublikasikan' : 'dijadikan draf';
        Flux::toast(variant: 'info', heading: 'Status Diperbarui', text: "Pengumuman '{$announcement->title}' berhasil {$status}.");
    }

    public function openDeleteModal(string $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->selectedAnnouncementId = $announcement->id;
        $this->title = $announcement->title;
        $this->showDeleteModal = true;
    }

    public function deleteAnnouncement(): void
    {
        $announcement = Announcement::findOrFail($this->selectedAnnouncementId);
        $title = $announcement->title;

        AuditLog::log(
            action: 'announcement_deleted',
            modelType: Announcement::class,
            modelId: $announcement->id,
            oldValues: ['title' => $announcement->title],
            newValues: null,
            reason: 'Penghapusan pengumuman oleh Administrator'
        );

        $announcement->delete();
        $this->showDeleteModal = false;

        Flux::toast(variant: 'danger', heading: 'Pengumuman Dihapus', text: "Pengumuman '{$title}' berhasil dihapus.");
    }

    public function render()
    {
        return view('livewire.admin.announcements-management', [
            'announcements' => $this->announcements,
        ]);
    }
}
