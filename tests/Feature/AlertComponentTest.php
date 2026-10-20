<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AlertComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_explicit_success_alert(): void
    {
        $rendered = Blade::render('<x-alert type="success" title="Operasi Sukses" message="Data berhasil disimpan ke sistem." />');

        $this->assertStringContainsString('Operasi Sukses', $rendered);
        $this->assertStringContainsString('Data berhasil disimpan ke sistem.', $rendered);
        $this->assertStringContainsString('data-variant="success"', $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_renders_explicit_danger_alert(): void
    {
        $rendered = Blade::render('<x-alert type="danger" title="Peringatan Keras" message="Terjadi galat tak terduga." />');

        $this->assertStringContainsString('Peringatan Keras', $rendered);
        $this->assertStringContainsString('Terjadi galat tak terduga.', $rendered);
        $this->assertStringContainsString('data-variant="danger"', $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_renders_explicit_warning_alert(): void
    {
        $rendered = Blade::render('<x-alert type="warning" title="Perhatian" message="Batas waktu pendaftaran hampir berakhir." />');

        $this->assertStringContainsString('Perhatian', $rendered);
        $this->assertStringContainsString('Batas waktu pendaftaran hampir berakhir.', $rendered);
        $this->assertStringContainsString('data-variant="warning"', $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_auto_detects_session_success(): void
    {
        session()->flash('success', 'Pendaftaran kontingen berhasil diverifikasi.');

        $rendered = Blade::render('<x-alert />');

        $this->assertStringContainsString('Berhasil Disimpan', $rendered);
        $this->assertStringContainsString('Pendaftaran kontingen berhasil diverifikasi.', $rendered);
        $this->assertStringContainsString('data-variant="success"', $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_auto_detects_session_error(): void
    {
        session()->flash('error', 'Token QR tidak valid atau kedaluwarsa.');

        $rendered = Blade::render('<x-alert />');

        $this->assertStringContainsString('Terjadi Kesalahan', $rendered);
        $this->assertStringContainsString('Token QR tidak valid atau kedaluwarsa.', $rendered);
        $this->assertStringContainsString('data-variant="danger"', $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_auto_detects_session_warning(): void
    {
        session()->flash('warning', 'Berkas bindamping belum lengkap.');

        $rendered = Blade::render('<x-alert />');

        $this->assertStringContainsString('Peringatan', $rendered);
        $this->assertStringContainsString('Berkas bindamping belum lengkap.', $rendered);
        $this->assertStringContainsString('data-variant="warning"', $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_auto_detects_form_validation_errors(): void
    {
        $errors = new ViewErrorBag();
        $messageBag = new MessageBag([
            'email' => ['Format email tidak valid.'],
            'password' => ['Kata sandi minimal 8 karakter.'],
        ]);
        $errors->put('default', $messageBag);
        view()->share('errors', $errors);

        $rendered = Blade::render('<x-alert />');

        $this->assertStringContainsString('Terdapat Kesalahan Pengisian Formulir', $rendered);
        $this->assertStringContainsString('Format email tidak valid.', $rendered);
        $this->assertStringContainsString('Kata sandi minimal 8 karakter.', $rendered);
        $this->assertStringContainsString('data-variant="danger"', $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_renders_nothing_when_there_is_no_message(): void
    {
        $rendered = Blade::render('<x-alert />');

        $this->assertStringNotContainsString('data-alert-toast', $rendered);
    }

    public function test_default_position_is_top_right(): void
    {
        $rendered = Blade::render('<x-alert type="info" message="Halo" />');

        $this->assertStringContainsString('data-position="top-right"', $rendered);
        $this->assertStringContainsString('top-4 right-4', $rendered);
    }

    public function test_position_prop_moves_toast(): void
    {
        $cases = [
            'top-left' => 'top-4 left-4',
            'bottom-left' => 'bottom-4 left-4',
            'bottom-right' => 'bottom-4 right-4',
            'center' => 'top-1/2 left-1/2',
            'bottom right' => 'bottom-4 right-4',
            'tidak-valid' => 'top-4 right-4',
        ];

        foreach ($cases as $position => $classes) {
            $rendered = Blade::render('<x-alert type="info" message="Halo" position="'.$position.'" />');

            $this->assertStringContainsString($classes, $rendered, "Posisi {$position}");
        }
    }

    public function test_layouts_render_flash_toast_once_without_per_page_alert(): void
    {
        $this->withSession(['success' => 'Layout menampilkan toast.'])
            ->get('/')
            ->assertOk()
            ->assertSee('Layout menampilkan toast.')
            ->assertSee('data-alert-toast', false);

        $html = $this->withSession(['success' => 'Sekali saja.'])->get(route('login'))->getContent();
        $this->assertSame(1, substr_count($html, 'Sekali saja.'));
    }
}
