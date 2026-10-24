<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Services\WebPushService;
use Flux\Flux;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Manajemen Publikasi Pengumuman Resmi Kegiatan (Phase 11)
 */
#[Layout('layouts.app')]
#[Title('Manajemen Pengumuman Kegiatan — Administrator')]
class AnnouncementsManagement extends Component
{
    use WithFileUploads, WithPagination;

    /** Batas berkas unduhan per pengumuman dan ukuran tiap berkas (KB) */
    private const MAX_FILES = 10;

    private const MAX_FILE_KB = 10240;

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

    /** Berkas yang baru dipilih untuk diunggah */
    public array $newFiles = [];

    /** Berkas unduhan yang sudah tersimpan dan tetap dipertahankan saat menyimpan */
    public array $keptAttachments = [];

    protected array $rules = [
        'title' => 'required|string|min:5|max:200',
        'category' => 'required|string|in:general,schedule,technical,urgent',
        'summary' => 'nullable|string|max:500',
        'content' => 'required|string|min:10',
        'is_published' => 'boolean',
        'broadcastPush' => 'boolean',
        'newFiles' => 'array',
        'newFiles.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip',
    ];

    protected array $messages = [
        'title.required' => 'Judul pengumuman wajib diisi.',
        'title.min' => 'Judul pengumuman minimal 5 karakter.',
        'content.required' => 'Isi materi pengumuman wajib dituliskan.',
        'content.min' => 'Isi materi pengumuman minimal 10 karakter.',
        'category.required' => 'Kategori pengumuman wajib dipilih.',
        'newFiles.*.max' => 'Ukuran tiap berkas maksimal 10 MB.',
        'newFiles.*.mimes' => 'Berkas harus berformat PDF, Word, Excel, PowerPoint, gambar (JPG/PNG), atau ZIP.',
        'newFiles.*.file' => 'Berkas unggahan tidak valid.',
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
        $this->newFiles = [];
        $this->keptAttachments = [];

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
        $this->newFiles = [];
        $this->keptAttachments = $announcement->attachmentFiles();

        $this->showFormModal = true;
    }

    /**
     * Lepas berkas tersimpan dari pengumuman (dihapus dari penyimpanan saat disimpan)
     */
    public function removeAttachment(int $index): void
    {
        unset($this->keptAttachments[$index]);
        $this->keptAttachments = array_values($this->keptAttachments);
    }

    /**
     * Batalkan satu berkas yang baru dipilih sebelum disimpan
     */
    public function removeNewFile(int $index): void
    {
        unset($this->newFiles[$index]);
        $this->newFiles = array_values($this->newFiles);
    }

    /**
     * Simpan berkas baru ke penyimpanan lalu gabungkan dengan berkas yang dipertahankan
     *
     * @return array<int, array{path: string, name: string, size: int}>
     */
    private function persistAttachments(): array
    {
        $files = array_values($this->keptAttachments);

        foreach ($this->newFiles as $upload) {
            /** @var UploadedFile $upload */
            $files[] = [
                'path' => $upload->store('announcements/attachments', 'public'),
                'name' => $this->safeFileName($upload->getClientOriginalName()),
                'size' => (int) $upload->getSize(),
            ];
        }

        return $files;
    }

    private function safeFileName(string $name): string
    {
        $clean = trim((string) preg_replace('/[^\w.\-() ]/u', '', $name));

        return Str::limit($clean !== '' ? $clean : 'berkas', 120, '');
    }

    public function saveAnnouncement(WebPushService $webPushService): void
    {
        $validated = $this->validate();

        if (count($this->keptAttachments) + count($this->newFiles) > self::MAX_FILES) {
            $this->addError('newFiles', 'Maksimal '.self::MAX_FILES.' berkas unduhan per pengumuman.');

            return;
        }

        if ($this->selectedAnnouncementId) {
            $announcement = Announcement::findOrFail($this->selectedAnnouncementId);
            $oldTitle = $announcement->title;

            // Berkas yang dilepas dari daftar dihapus dari penyimpanan
            $keptPaths = array_column($this->keptAttachments, 'path');
            $removed = array_diff(array_column($announcement->attachmentFiles(), 'path'), $keptPaths);
            if ($removed) {
                Storage::disk('public')->delete($removed);
            }

            $announcement->update([
                'attachments' => $this->persistAttachments() ?: null,
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
                'attachments' => $this->persistAttachments() ?: null,
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
        $this->newFiles = [];
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
