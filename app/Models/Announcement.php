<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Model pengumuman resmi LT III
 */
class Announcement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'category',
        'summary',
        'content',
        'attachments',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'attachments' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Announcement $announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title).'-'.Str::random(5);
            }
            if ($announcement->is_published && empty($announcement->published_at)) {
                $announcement->published_at = now();
            }
        });

        // Berkas unduhan ikut dihapus dari penyimpanan saat pengumuman dihapus
        static::deleting(function (Announcement $announcement) {
            $paths = array_column($announcement->attachmentFiles(), 'path');

            if ($paths) {
                Storage::disk('public')->delete($paths);
            }
        });
    }

    /**
     * Scope untuk pengumuman yang sudah terpublikasi
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at');
    }

    /**
     * Berkas unduhan pengumuman, berurutan sesuai indeks yang dipakai rute unduh
     *
     * @return array<int, array{path: string, name: string, size: int}>
     */
    public function attachmentFiles(): array
    {
        return array_values(array_filter((array) $this->attachments, fn ($file) => is_array($file) && ! empty($file['path'])));
    }

    /**
     * Ukuran berkas dalam format mudah dibaca (B, KB, MB)
     */
    public static function formatFileSize(int $bytes): string
    {
        return match (true) {
            $bytes >= 1048576 => number_format($bytes / 1048576, 1, ',', '.').' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 0, ',', '.').' KB',
            default => $bytes.' B',
        };
    }

    /**
     * Penulis pengumuman
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Badge warna kategori
     */
    public function categoryBadge(): string
    {
        return match ($this->category) {
            'urgent' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-950/60 dark:text-red-300',
            'technical' => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300',
            'schedule' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300',
        };
    }
}
