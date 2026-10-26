<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Event;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Konfigurasi pendaftaran mandiri aparatur (tautan rahasia, jadwal, dan status buka/tutup) oleh Administrator/Superadmin.
 * Membuat ulang kode membuat tautan lama tidak berlaku.
 */
#[Lazy]
class OfficialRegistrationLink extends Component
{
    public bool $is_official_registration_open = false;

    public ?string $official_registration_start_at = null;

    public ?string $official_registration_end_at = null;

    public function placeholder(): string
    {
        return '<div class="h-[76px] animate-pulse rounded-2xl bg-zinc-100 dark:bg-zinc-800/60"></div>';
    }

    public function mount(): void
    {
        $event = Event::first();

        $this->is_official_registration_open = (bool) $event?->is_official_registration_open;
        $this->official_registration_start_at = $event?->official_registration_start_at?->format('Y-m-d\TH:i');
        $this->official_registration_end_at = $event?->official_registration_end_at?->format('Y-m-d\TH:i');
    }

    private function authorizeManager(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    /**
     * Simpan status dan jadwal buka-tutup pendaftaran aparatur
     */
    public function saveSettings(): void
    {
        $this->authorizeManager();

        $validated = $this->validate([
            'is_official_registration_open' => 'boolean',
            'official_registration_start_at' => 'nullable|date',
            'official_registration_end_at' => 'nullable|date|after_or_equal:official_registration_start_at',
        ], [
            'official_registration_end_at.after_or_equal' => 'Waktu penutupan harus setelah waktu pembukaan.',
        ]);

        $event = Event::first();
        if (! $event) {
            return;
        }

        $keys = ['is_official_registration_open', 'official_registration_start_at', 'official_registration_end_at'];
        $oldValues = $event->only($keys);

        $event->update([
            'is_official_registration_open' => $validated['is_official_registration_open'],
            'official_registration_start_at' => $validated['official_registration_start_at'] ?: null,
            'official_registration_end_at' => $validated['official_registration_end_at'] ?: null,
        ]);

        AuditLog::log(
            action: 'official_registration_settings_updated',
            modelType: Event::class,
            modelId: $event->id,
            oldValues: $oldValues,
            newValues: $event->only($keys),
            userId: Auth::id(),
        );

        Flux::toast(variant: 'success', heading: 'Tersimpan', text: 'Pengaturan pendaftaran mandiri aparatur diperbarui.');
    }

    /**
     * Membuat atau mengganti kode rahasia tautan
     */
    public function regenerate(): void
    {
        $this->authorizeManager();

        $event = Event::first();
        if (! $event) {
            return;
        }

        $hadToken = (bool) $event->official_registration_token;
        $event->update(['official_registration_token' => Str::random(32)]);

        AuditLog::log(
            action: $hadToken ? 'official_registration_token_regenerated' : 'official_registration_token_created',
            modelType: Event::class,
            modelId: $event->id,
            userId: Auth::id(),
        );

        Flux::toast(
            variant: 'success',
            heading: 'Tautan Dibuat',
            text: $hadToken ? 'Kode baru dibuat; tautan lama tidak berlaku lagi.' : 'Tautan pendaftaran aparatur berhasil dibuat.'
        );
    }

    public function render()
    {
        $event = Event::first();

        return view('livewire.admin.official-registration-link', [
            'url' => $event?->officialRegistrationUrl(),
            'isOpen' => (bool) $event?->isOfficialRegistrationOpen(),        ]);
    }
}
