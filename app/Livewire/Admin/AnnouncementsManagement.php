<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Services\WebPushService;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Manajemen Publikasi Pengumuman Resmi Kegiatan (Phase 11)
 */
#[Layout('layouts.app')]
#[Title('Manajemen Pengumuman Kegiatan — Administrator')]
class AnnouncementsManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';

    // Modal state
    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public ?string $selectedAnnouncementId = null;

    // Form fields
    public string $title = '';
    public string $category = 'general';
    public string $summary = '';
    public string $content = '';
    public bool $is_published = true;
    public bool $broadcastPush = true;

    protected array $rules = [
        'title' => 'required|string|min:5|max:200',
        'category' => 'required|string|in:general,schedule,technical,urgent',
        'summary' => 'nullable|string|max:500',
        'content' => 'required|string|min:10',
        'is_published' => 'boolean',
        'broadcastPush' => 'boolean',
    ];

    protected array $messages = [
        'title.required' => 'Judul pengumuman wajib diisi.',
        'title.min' => 'Judul pengumuman minimal 5 karakter.',
        'content.required' => 'Isi materi pengumuman wajib dituliskan.',
        'content.min' => 'Isi materi pengumuman minimal 10 karakter.',
        'category.required' => 'Kategori pengumuman wajib dipilih.',
    ];

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

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->selectedAnnouncementId = null;
        $this->title = '';
        $this->category = 'general';
        $this->summary = '';
        $this->content = '';
        $this->is_published = true;
        $this->broadcastPush = true;

        $this->showFormModal = true;
    }

    public function openEditModal(string $id): void
    {
        $this->resetValidation();
        $announcement = Announcement::findOrFail($id);

        $this->selectedAnnouncementId = $announcement->id;
        $this->title = $announcement->title;
        $this->category = $announcement->category;
        $this->summary = $announcement->summary ?? '';
        $this->content = $announcement->content;
        $this->is_published = (bool) $announcement->is_published;
        $this->broadcastPush = false;

        $this->showFormModal = true;
    }

    public function saveAnnouncement(WebPushService $webPushService): void
    {
        $validated = $this->validate();

        if ($this->selectedAnnouncementId) {
            $announcement = Announcement::findOrFail($this->selectedAnnouncementId);
            $oldTitle = $announcement->title;

            $announcement->update([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'summary' => $validated['summary'] ?: null,
                'content' => $validated['content'],
                'is_published' => $validated['is_published'],
                'published_at' => $validated['is_published'] ? ($announcement->published_at ?? now()) : null,
            ]);

            AuditLog::log(
                action: 'announcement_updated',
                modelType: Announcement::class,
                modelId: $announcement->id,
                oldValues: ['title' => $oldTitle],
                newValues: ['title' => $announcement->title],
                reason: 'Pembaruan pengumuman oleh Administrator'
            );

            session()->flash('success', "Pengumuman '{$announcement->title}' berhasil diperbarui.");
            Flux::toast(variant: 'success', heading: 'Pengumuman Diperbarui', text: "Pengumuman '{$announcement->title}' berhasil diperbarui.");
        } else {
            $announcement = Announcement::create([
                'author_id' => auth()->user()->recordableId(),
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']) . '-' . Str::random(5),
                'category' => $validated['category'],
                'summary' => $validated['summary'] ?: null,
                'content' => $validated['content'],
                'is_published' => $validated['is_published'],
                'published_at' => $validated['is_published'] ? now() : null,
            ]);

            AuditLog::log(
                action: 'announcement_created',
                modelType: Announcement::class,
                modelId: $announcement->id,
                oldValues: null,
                newValues: ['title' => $announcement->title, 'category' => $announcement->category],
                reason: 'Pembuatan pengumuman baru oleh Administrator'
            );

            // Otomatis kirim notifikasi PWA Web Push saat pengumuman ditambahkan dan dipublikasikan
            if ($this->broadcastPush && $announcement->is_published) {
                $webPushService->broadcastNotification(
                    payload: [
                        'title' => 'Pengumuman: ' . $announcement->title,
                        'body' => $announcement->summary ?: Str::limit(strip_tags($announcement->content), 120),
                        'url' => route('announcements.show', $announcement->slug),
                        'tag' => 'lt3-announcement-' . $announcement->id,
                        'icon' => '/logo.png',
                        'badge' => '/favicon.png',
                    ],
                    targetRole: null,
                    sender: auth()->user()
                );
            }

            session()->flash('success', "Pengumuman '{$announcement->title}' berhasil dipublikasikan dan notifikasi PWA telah dikirim.");
            Flux::toast(variant: 'success', heading: 'Pengumuman Dipublikasikan', text: "Pengumuman '{$announcement->title}' berhasil dipublikasikan dan notifikasi PWA telah dikirim.");
        }

        $this->showFormModal = false;
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
        session()->flash('success', "Pengumuman '{$announcement->title}' berhasil {$status}.");
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

        session()->flash('success', "Pengumuman '{$title}' berhasil dihapus.");
        Flux::toast(variant: 'danger', heading: 'Pengumuman Dihapus', text: "Pengumuman '{$title}' berhasil dihapus.");
    }

    public function render()
    {
        return view('livewire.admin.announcements-management', [
            'announcements' => $this->announcements,
        ]);
    }
}
