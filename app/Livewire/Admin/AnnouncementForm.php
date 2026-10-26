<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Services\WebPushService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Halaman tulis / ubah pengumuman resmi beserta berkas unduhannya
 */
#[Layout('layouts.app')]
class AnnouncementForm extends Component
{
    use WithFileUploads;

    /** Batas jumlah berkas unduhan per pengumuman */
    private const MAX_FILES = 10;

    public ?string $announcementId = null;

    public string $title = '';

    public string $category = 'general';

    public string $summary = '';

    public string $content = '';

    public bool $is_published = true;

    public bool $broadcastPush = true;

    /** Berkas yang baru dipilih untuk diunggah */
    public array $newFiles = [];

    /** Berkas unduhan tersimpan yang tetap dipertahankan saat menyimpan */
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

    public function mount(?string $id = null): void
    {
        if (! $id) {
            return;
        }

        $announcement = Announcement::findOrFail($id);

        $this->announcementId = $announcement->id;
        $this->title = $announcement->title;
        $this->category = $announcement->category;
        $this->summary = $announcement->summary ?? '';
        $this->content = $announcement->content;
        $this->is_published = (bool) $announcement->is_published;
        $this->broadcastPush = false;
        $this->keptAttachments = $announcement->attachmentFiles();
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

    public function save(WebPushService $webPushService): void
    {
        $validated = $this->validate();

        if (count($this->keptAttachments) + count($this->newFiles) > self::MAX_FILES) {
            $this->addError('newFiles', 'Maksimal '.self::MAX_FILES.' berkas unduhan per pengumuman.');

            return;
        }

        $announcement = $this->announcementId ? $this->update($validated) : $this->create($validated, $webPushService);

        session()->flash('success', $this->announcementId
            ? "Pengumuman '{$announcement->title}' berhasil diperbarui."
            : "Pengumuman '{$announcement->title}' berhasil disimpan.");

        $this->redirectRoute('admin.announcements.index', navigate: true);
    }

    private function update(array $validated): Announcement
    {
        $announcement = Announcement::findOrFail($this->announcementId);
        $oldTitle = $announcement->title;

        // Berkas yang dilepas dari daftar dihapus dari penyimpanan
        $removed = array_diff(array_column($announcement->attachmentFiles(), 'path'), array_column($this->keptAttachments, 'path'));
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

        return $announcement;
    }

    private function create(array $validated, WebPushService $webPushService): Announcement
    {
        $announcement = Announcement::create([
            'author_id' => auth()->user()->recordableId(),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::random(5),
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

        // Notifikasi PWA otomatis saat pengumuman baru langsung diterbitkan
        if ($this->broadcastPush && $announcement->is_published) {
            $webPushService->broadcastNotification(
                payload: [
                    'title' => 'Pengumuman: '.$announcement->title,
                    'body' => $announcement->summary ?: Str::limit(strip_tags($announcement->content), 120),
                    'url' => route('announcements.show', $announcement->slug),
                    'tag' => 'lt3-announcement-'.$announcement->id,
                    'icon' => '/logo.png',
                    'badge' => '/favicon.png',
                ],
                targetRole: null,
                sender: auth()->user()
            );
        }

        return $announcement;
    }

    public function render()
    {
        return view('livewire.admin.announcement-form')
            ->title($this->announcementId ? 'Edit Pengumuman — Administrator' : 'Tulis Pengumuman — Administrator');
    }
}
