<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Endpoint berkas publik. Seluruh halaman publik adalah komponen Livewire (App\Livewire\Public).
 */
class PublicPageController extends Controller
{
    /**
     * Unduh berkas lampiran pengumuman yang sudah terbit, memakai nama berkas aslinya
     */
    public function announcementDownload(string $slug, int $index): StreamedResponse
    {
        $announcement = Announcement::published()->where('slug', $slug)->firstOrFail();
        $file = $announcement->attachmentFiles()[$index] ?? abort(404);

        abort_unless(Storage::disk('public')->exists($file['path']), 404);

        return Storage::disk('public')->download($file['path'], $file['name']);
    }
}
