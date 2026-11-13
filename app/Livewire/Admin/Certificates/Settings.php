<?php

namespace App\Livewire\Admin\Certificates;

use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Komponen pengaturan template sertifikat, tata letak visual, dan toggle penerbitan
 */
#[Layout('layouts.app')]
#[Title('Pengaturan Template Sertifikat')]
class Settings extends Component
{
    use WithFileUploads;

    public ?Event $event = null;

    /** Status penerbitan sertifikat */
    public bool $is_certificate_published = false;

    /** Tanda Tangan & Penandatangan Dokumen Resmi */
    public string $committee_chair_name = '';

    public string $committee_chair_nta = '';

    public $committee_signature_file;

    public ?string $committee_signature_url = null;

    public string $kwarcab_chair_name = '';

    public string $kwarcab_chair_nta = '';

    public $kwarcab_signature_file;

    public ?string $kwarcab_signature_url = null;

    public bool $show_signatures_on_certificate = true;

    /** Tab peran aktif: participant, chaperone, committee, judge */
    public string $activeRole = CertificateService::ROLE_PARTICIPANT;

    /** Berkas gambar template yang diunggah */
    public $template_file;

    public ?string $template_url = null;

    /** Koordinat dan gaya: Nama Lengkap */
    public float $name_x = 50.0;

    public float $name_y = 45.0;

    public float $name_size = 28.0;

    public string $name_color = '#111827';

    public string $name_align = 'center';

    public bool $name_bold = true;

    /** Koordinat dan gaya: NTA */
    public float $scout_id_x = 50.0;

    public float $scout_id_y = 51.5;

    public float $scout_id_size = 13.0;

    public string $scout_id_color = '#4b5563';

    public string $scout_id_align = 'center';

    public bool $scout_id_bold = false;

    /** Koordinat dan gaya: Gugusdepan / Pangkalan */
    public float $inst_x = 50.0;

    public float $inst_y = 57.5;

    public float $inst_size = 15.0;

    public string $inst_color = '#1f2937';

    public string $inst_align = 'center';

    public bool $inst_bold = false;

    /** Koordinat dan gaya: Sebagai (Role) */
    public float $role_x = 50.0;

    public float $role_y = 64.5;

    public float $role_size = 16.0;

    public string $role_color = '#b45309';

    public string $role_align = 'center';

    public bool $role_bold = true;

    /** Koordinat dan gaya: Nomor Sertifikat */
    public float $cert_no_x = 50.0;

    public float $cert_no_y = 28.0;

    public float $cert_no_size = 11.0;

    public string $cert_no_color = '#6b7280';

    public string $cert_no_align = 'center';

    public bool $cert_no_bold = false;

    public function mount(CertificateService $certificateService): void
    {
        $this->event = Event::where('is_active', true)->first() ?? Event::first();
        if ($this->event) {
            $this->is_certificate_published = (bool) $this->event->is_certificate_published;
            $this->committee_chair_name = (string) ($this->event->committee_chair_name ?? '');
            $this->committee_chair_nta = (string) ($this->event->committee_chair_nta ?? '');
            $this->committee_signature_url = $this->event->committeeSignatureUrl();

            $this->kwarcab_chair_name = (string) ($this->event->kwarcab_chair_name ?? '');
            $this->kwarcab_chair_nta = (string) ($this->event->kwarcab_chair_nta ?? '');
            $this->kwarcab_signature_url = $this->event->kwarcabSignatureUrl();

            $this->show_signatures_on_certificate = (bool) ($this->event->show_signatures_on_certificate ?? true);
        }

        $this->loadRoleConfig($certificateService);
    }

    /**
     * Memuat konfigurasi tata letak dan gambar untuk peran terpilih
     */
    public function loadRoleConfig(CertificateService $certificateService): void
    {
        if (! $this->event) {
            return;
        }

        $this->template_file = null;
        $this->template_url = $certificateService->getTemplateUrl($this->event, $this->activeRole);
        $layout = $certificateService->getLayout($this->event, $this->activeRole);

        // Name
        $this->name_x = (float) ($layout['full_name']['x'] ?? 50.0);
        $this->name_y = (float) ($layout['full_name']['y'] ?? 45.0);
        $this->name_size = (float) ($layout['full_name']['font_size'] ?? 28.0);
        $this->name_color = (string) ($layout['full_name']['color'] ?? '#111827');
        $this->name_align = (string) ($layout['full_name']['align'] ?? 'center');
        $this->name_bold = (bool) ($layout['full_name']['bold'] ?? true);

        // Scout ID (NTA)
        $this->scout_id_x = (float) ($layout['scout_id']['x'] ?? 50.0);
        $this->scout_id_y = (float) ($layout['scout_id']['y'] ?? 51.5);
        $this->scout_id_size = (float) ($layout['scout_id']['font_size'] ?? 13.0);
        $this->scout_id_color = (string) ($layout['scout_id']['color'] ?? '#4b5563');
        $this->scout_id_align = (string) ($layout['scout_id']['align'] ?? 'center');
        $this->scout_id_bold = (bool) ($layout['scout_id']['bold'] ?? false);

        // Institution
        $this->inst_x = (float) ($layout['institution']['x'] ?? 50.0);
        $this->inst_y = (float) ($layout['institution']['y'] ?? 57.5);
        $this->inst_size = (float) ($layout['institution']['font_size'] ?? 15.0);
        $this->inst_color = (string) ($layout['institution']['color'] ?? '#1f2937');
        $this->inst_align = (string) ($layout['institution']['align'] ?? 'center');
        $this->inst_bold = (bool) ($layout['institution']['bold'] ?? false);

        // Role Label
        $this->role_x = (float) ($layout['role_label']['x'] ?? 50.0);
        $this->role_y = (float) ($layout['role_label']['y'] ?? 64.5);
        $this->role_size = (float) ($layout['role_label']['font_size'] ?? 16.0);
        $this->role_color = (string) ($layout['role_label']['color'] ?? '#b45309');
        $this->role_align = (string) ($layout['role_label']['align'] ?? 'center');
        $this->role_bold = (bool) ($layout['role_label']['bold'] ?? true);

        // Certificate Number
        $this->cert_no_x = (float) ($layout['certificate_number']['x'] ?? 50.0);
        $this->cert_no_y = (float) ($layout['certificate_number']['y'] ?? 28.0);
        $this->cert_no_size = (float) ($layout['certificate_number']['font_size'] ?? 11.0);
        $this->cert_no_color = (string) ($layout['certificate_number']['color'] ?? '#6b7280');
        $this->cert_no_align = (string) ($layout['certificate_number']['align'] ?? 'center');
        $this->cert_no_bold = (bool) ($layout['certificate_number']['bold'] ?? false);
    }

    /**
     * Berpindah tab peran
     */
    public function setRole(string $role, CertificateService $certificateService): void
    {
        if (! array_key_exists($role, CertificateService::ROLES)) {
            return;
        }

        $this->activeRole = $role;
        $this->loadRoleConfig($certificateService);
    }

    /**
     * Mengalihkan status penerbitan sertifikat (ON / OFF)
     */
    public function togglePublish(): void
    {
        if (! $this->event) {
            return;
        }

        $this->is_certificate_published = ! $this->is_certificate_published;
        $this->event->update([
            'is_certificate_published' => $this->is_certificate_published,
        ]);

        $statusText = $this->is_certificate_published ? 'DIBUKA (ON)' : 'DITUTUP (OFF)';
        session()->flash('status', "Status penerbitan sertifikat berhasil diubah menjadi: {$statusText}.");
    }

    /**
     * Menyusun array struktur layout saat ini
     */
    protected function currentLayoutArray(): array
    {
        return [
            'certificate_number' => [
                'x' => round((float) $this->cert_no_x, 2),
                'y' => round((float) $this->cert_no_y, 2),
                'font_size' => round((float) $this->cert_no_size, 1),
                'color' => $this->cert_no_color,
                'align' => $this->cert_no_align,
                'bold' => (bool) $this->cert_no_bold,
            ],
            'full_name' => [
                'x' => round((float) $this->name_x, 2),
                'y' => round((float) $this->name_y, 2),
                'font_size' => round((float) $this->name_size, 1),
                'color' => $this->name_color,
                'align' => $this->name_align,
                'bold' => (bool) $this->name_bold,
            ],
            'scout_id' => [
                'x' => round((float) $this->scout_id_x, 2),
                'y' => round((float) $this->scout_id_y, 2),
                'font_size' => round((float) $this->scout_id_size, 1),
                'color' => $this->scout_id_color,
                'align' => $this->scout_id_align,
                'bold' => (bool) $this->scout_id_bold,
            ],
            'institution' => [
                'x' => round((float) $this->inst_x, 2),
                'y' => round((float) $this->inst_y, 2),
                'font_size' => round((float) $this->inst_size, 1),
                'color' => $this->inst_color,
                'align' => $this->inst_align,
                'bold' => (bool) $this->inst_bold,
            ],
            'role_label' => [
                'x' => round((float) $this->role_x, 2),
                'y' => round((float) $this->role_y, 2),
                'font_size' => round((float) $this->role_size, 1),
                'color' => $this->role_color,
                'align' => $this->role_align,
                'bold' => (bool) $this->role_bold,
            ],
        ];
    }

    /**
     * Menyimpan konfigurasi peran yang sedang aktif
     */
    public function save(CertificateService $certificateService): void
    {
        if (! $this->event) {
            return;
        }

        $this->validate([
            'template_file' => 'nullable|image|max:5120',
            'name_x' => 'required|numeric|between:0,100',
            'name_y' => 'required|numeric|between:0,100',
            'name_size' => 'required|numeric|between:8,72',
            'name_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'name_align' => 'required|in:left,center,right',
            'name_bold' => 'boolean',

            'scout_id_x' => 'required|numeric|between:0,100',
            'scout_id_y' => 'required|numeric|between:0,100',
            'scout_id_size' => 'required|numeric|between:8,72',
            'scout_id_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'scout_id_align' => 'required|in:left,center,right',
            'scout_id_bold' => 'boolean',

            'inst_x' => 'required|numeric|between:0,100',
            'inst_y' => 'required|numeric|between:0,100',
            'inst_size' => 'required|numeric|between:8,72',
            'inst_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'inst_align' => 'required|in:left,center,right',
            'inst_bold' => 'boolean',

            'role_x' => 'required|numeric|between:0,100',
            'role_y' => 'required|numeric|between:0,100',
            'role_size' => 'required|numeric|between:8,72',
            'role_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'role_align' => 'required|in:left,center,right',
            'role_bold' => 'boolean',

            'cert_no_x' => 'required|numeric|between:0,100',
            'cert_no_y' => 'required|numeric|between:0,100',
            'cert_no_size' => 'required|numeric|between:8,72',
            'cert_no_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'cert_no_align' => 'required|in:left,center,right',
            'cert_no_bold' => 'boolean',
        ]);

        $template = CertificateTemplate::firstOrNew([
            'event_id' => $this->event->id,
            'role' => $this->activeRole,
        ]);

        if ($this->template_file) {
            if ($template->template_path && Storage::disk('public')->exists($template->template_path)) {
                Storage::disk('public')->delete($template->template_path);
            }

            $stored = $this->template_file->store('certificates/templates', 'public');
            $template->template_path = $stored;
            $this->template_file = null;
        }

        $template->layout = $this->currentLayoutArray();
        $template->save();

        $this->template_url = $certificateService->getTemplateUrl($this->event, $this->activeRole);
        $roleLabel = CertificateService::ROLES[$this->activeRole] ?? $this->activeRole;

        session()->flash('status', "Pengaturan sertifikat untuk {$roleLabel} berhasil disimpan.");
    }

    /**
     * Menerapkan tata letak saat ini ke seluruh peran lainnya
     */
    public function applyToAllRoles(CertificateService $certificateService): void
    {
        if (! $this->event) {
            return;
        }

        $layout = $this->currentLayoutArray();

        foreach (array_keys(CertificateService::ROLES) as $role) {
            $template = CertificateTemplate::firstOrNew([
                'event_id' => $this->event->id,
                'role' => $role,
            ]);
            $template->layout = $layout;
            $template->save();
        }

        session()->flash('status', 'Tata letak berhasil diterapkan ke seluruh peran (Peserta, Pembina, Panitia, Dewan Juri).');
    }

    /**
     * Menghapus gambar template kustom dan kembali ke desain bingkai bawaan
     */
    public function deleteTemplate(CertificateService $certificateService): void
    {
        if (! $this->event) {
            return;
        }

        $template = CertificateTemplate::where('event_id', $this->event->id)
            ->where('role', $this->activeRole)
            ->first();

        if ($template && $template->template_path) {
            if (Storage::disk('public')->exists($template->template_path)) {
                Storage::disk('public')->delete($template->template_path);
            }
            $template->template_path = null;
            $template->save();
        }

        $this->template_file = null;
        $this->template_url = null;

        session()->flash('status', 'Gambar template berhasil dihapus. Sertifikat kembali ke bingkai bawaan.');
    }

    /**
     * Menyimpan data dan berkas tanda tangan Ketua Panitia & Ketua Kwartir
     */
    public function saveSignatories(): void
    {
        if (! $this->event) {
            return;
        }

        $this->validate([
            'committee_chair_name' => 'nullable|string|max:255',
            'committee_chair_nta' => 'nullable|string|max:100',
            'committee_signature_file' => 'nullable|image|max:2048',
            'kwarcab_chair_name' => 'nullable|string|max:255',
            'kwarcab_chair_nta' => 'nullable|string|max:100',
            'kwarcab_signature_file' => 'nullable|image|max:2048',
            'show_signatures_on_certificate' => 'boolean',
        ]);

        $updateData = [
            'committee_chair_name' => $this->committee_chair_name ?: null,
            'committee_chair_nta' => $this->committee_chair_nta ?: null,
            'kwarcab_chair_name' => $this->kwarcab_chair_name ?: null,
            'kwarcab_chair_nta' => $this->kwarcab_chair_nta ?: null,
            'show_signatures_on_certificate' => (bool) $this->show_signatures_on_certificate,
        ];

        if ($this->committee_signature_file) {
            if ($this->event->committee_chair_signature_path && Storage::disk('public')->exists($this->event->committee_chair_signature_path)) {
                Storage::disk('public')->delete($this->event->committee_chair_signature_path);
            }
            $updateData['committee_chair_signature_path'] = $this->committee_signature_file->store('certificates/signatures', 'public');
            $this->committee_signature_file = null;
        }

        if ($this->kwarcab_signature_file) {
            if ($this->event->kwarcab_chair_signature_path && Storage::disk('public')->exists($this->event->kwarcab_chair_signature_path)) {
                Storage::disk('public')->delete($this->event->kwarcab_chair_signature_path);
            }
            $updateData['kwarcab_chair_signature_path'] = $this->kwarcab_signature_file->store('certificates/signatures', 'public');
            $this->kwarcab_signature_file = null;
        }

        $this->event->update($updateData);
        $this->event->refresh();

        $this->committee_signature_url = $this->event->committeeSignatureUrl();
        $this->kwarcab_signature_url = $this->event->kwarcabSignatureUrl();

        session()->flash('signatory_status', 'Tanda tangan dan data penandatangan sertifikat berhasil diperbarui.');
    }

    /**
     * Menghapus berkas tanda tangan Ketua Panitia
     */
    public function deleteCommitteeSignature(): void
    {
        if (! $this->event) {
            return;
        }

        if ($this->event->committee_chair_signature_path && Storage::disk('public')->exists($this->event->committee_chair_signature_path)) {
            Storage::disk('public')->delete($this->event->committee_chair_signature_path);
        }

        $this->event->update(['committee_chair_signature_path' => null]);
        $this->committee_signature_file = null;
        $this->committee_signature_url = null;

        session()->flash('signatory_status', 'Tanda tangan Ketua Panitia berhasil dihapus.');
    }

    /**
     * Menghapus berkas tanda tangan Ketua Kwartir Cabang
     */
    public function deleteKwarcabSignature(): void
    {
        if (! $this->event) {
            return;
        }

        if ($this->event->kwarcab_chair_signature_path && Storage::disk('public')->exists($this->event->kwarcab_chair_signature_path)) {
            Storage::disk('public')->delete($this->event->kwarcab_chair_signature_path);
        }

        $this->event->update(['kwarcab_chair_signature_path' => null]);
        $this->kwarcab_signature_file = null;
        $this->kwarcab_signature_url = null;

        session()->flash('signatory_status', 'Tanda tangan Ketua Kwartir Cabang berhasil dihapus.');
    }

    public function render(CertificateService $certificateService)
    {
        $sample = $certificateService->getSampleRecipient($this->activeRole, $this->event);

        return view('livewire.admin.certificates.settings', [
            'sample' => $sample,
            'roles' => CertificateService::ROLES,
        ]);
    }
}
