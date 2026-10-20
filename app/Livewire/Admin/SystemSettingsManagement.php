<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Event;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Konfigurasi Induk Kegiatan & Pengaturan Sistem (Phase 11)
 */
#[Layout('layouts.app')]
#[Title('Konfigurasi Sistem & Master Kegiatan — Administrator')]
class SystemSettingsManagement extends Component
{
    public ?string $eventId = null;
    public string $title = '';
    public string $theme = '';
    public string $description = '';
    public string $location = '';
    public string $start_date = '';
    public string $end_date = '';
    public ?string $registration_start_at = null;
    public ?string $registration_end_at = null;
    public bool $is_registration_open = true;
    public string $contact_email = '';
    public string $contact_phone = '';
    public bool $is_active = true;
    public bool $hide_patrol_identities = false;
    public string $committee_chair_name = '';
    public string $committee_chair_nta = '';
    public string $judge_chair_name = '';
    public string $judge_chair_nta = '';
    public string $kwarcab_chair_name = '';
    public string $kwarcab_chair_nta = '';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:200',
            'theme' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
            'location' => 'required|string|max:200',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_start_at' => 'nullable|date',
            'registration_end_at' => 'nullable|date|after_or_equal:registration_start_at',
            'is_registration_open' => 'boolean',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'hide_patrol_identities' => 'boolean',
            'committee_chair_name' => 'nullable|string|max:150',
            'committee_chair_nta' => 'nullable|string|max:150',
            'judge_chair_name' => 'nullable|string|max:150',
            'judge_chair_nta' => 'nullable|string|max:150',
            'kwarcab_chair_name' => 'nullable|string|max:150',
            'kwarcab_chair_nta' => 'nullable|string|max:150',
        ];
    }

    protected array $messages = [
        'title.required' => 'Nama kegiatan resmi wajib diisi.',
        'location.required' => 'Lokasi kegiatan bumi perkemahan wajib diisi.',
        'start_date.required' => 'Tanggal mulai kegiatan wajib ditentukan.',
        'end_date.required' => 'Tanggal selesai kegiatan wajib ditentukan.',
        'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        'registration_end_at.after_or_equal' => 'Batas akhir registrasi harus setelah waktu mulai registrasi.',
    ];

    public function mount(): void
    {
        $event = Event::first();

        if ($event) {
            $this->eventId = $event->id;
            $this->title = $event->title;
            $this->theme = $event->theme ?? '';
            $this->description = $event->description ?? '';
            $this->location = $event->location;
            $this->start_date = $event->start_date ? $event->start_date->format('Y-m-d') : '';
            $this->end_date = $event->end_date ? $event->end_date->format('Y-m-d') : '';
            $this->registration_start_at = $event->registration_start_at ? $event->registration_start_at->format('Y-m-d\TH:i') : null;
            $this->registration_end_at = $event->registration_end_at ? $event->registration_end_at->format('Y-m-d\TH:i') : null;
            $this->is_registration_open = (bool) $event->is_registration_open;
            $this->contact_email = $event->contact_email ?? '';
            $this->contact_phone = $event->contact_phone ?? '';
            $this->is_active = (bool) $event->is_active;
            $this->hide_patrol_identities = (bool) ($event->hide_patrol_identities ?? false);
            $this->committee_chair_name = $event->committee_chair_name ?? '';
            $this->committee_chair_nta = $event->committee_chair_nta ?? '';
            $this->judge_chair_name = $event->judge_chair_name ?? '';
            $this->judge_chair_nta = $event->judge_chair_nta ?? '';
            $this->kwarcab_chair_name = $event->kwarcab_chair_name ?? '';
            $this->kwarcab_chair_nta = $event->kwarcab_chair_nta ?? '';
        }
    }

    public function saveSettings(): void
    {
        $validated = $this->validate();

        $event = Event::first();

        if (! $event) {
            $event = new Event();
        }

        $oldValues = $event->only([
            'title', 'theme', 'location', 'start_date', 'end_date',
            'registration_start_at', 'registration_end_at', 'is_registration_open',
            'hide_patrol_identities',
            'committee_chair_name',
            'committee_chair_nta',
            'judge_chair_name',
            'judge_chair_nta',
            'kwarcab_chair_name',
            'kwarcab_chair_nta',
        ]);

        $event->fill([
            'title' => $validated['title'],
            'theme' => $validated['theme'] ?: null,
            'description' => $validated['description'] ?: null,
            'location' => $validated['location'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'registration_start_at' => $validated['registration_start_at'] ?: null,
            'registration_end_at' => $validated['registration_end_at'] ?: null,
            'is_registration_open' => $validated['is_registration_open'],
            'contact_email' => $validated['contact_email'] ?: null,
            'contact_phone' => $validated['contact_phone'] ?: null,
            'is_active' => $validated['is_active'],
            'hide_patrol_identities' => $validated['hide_patrol_identities'],
            'committee_chair_name' => $validated['committee_chair_name'] ?: null,
            'committee_chair_nta' => $validated['committee_chair_nta'] ?: null,
            'judge_chair_name' => $validated['judge_chair_name'] ?: null,
            'judge_chair_nta' => $validated['judge_chair_nta'] ?: null,
            'kwarcab_chair_name' => $validated['kwarcab_chair_name'] ?: null,
            'kwarcab_chair_nta' => $validated['kwarcab_chair_nta'] ?: null,
        ]);

        $event->save();

        AuditLog::log(
            action: 'event_settings_updated',
            modelType: Event::class,
            modelId: $event->id,
            oldValues: $oldValues,
            newValues: $event->only(array_keys($oldValues)),
            reason: 'Pembaruan konfigurasi master kegiatan oleh Administrator'
        );

        session()->flash('success', 'Konfigurasi master kegiatan dan sistem berhasil diperbarui.');
        Flux::toast(variant: 'success', heading: 'Pengaturan Disimpan', text: 'Konfigurasi master kegiatan dan sistem berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.admin.system-settings-management');
    }
}
