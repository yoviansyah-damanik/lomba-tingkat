<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AlertComponentTest extends TestCase
{
    public function test_renders_explicit_success_alert(): void
    {
        $rendered = Blade::render('<x-alert type="success" title="Operasi Sukses" message="Data berhasil disimpan ke sistem." />');

        $this->assertStringContainsString('Operasi Sukses', $rendered);
        $this->assertStringContainsString('Data berhasil disimpan ke sistem.', $rendered);
        $this->assertStringContainsString("variant: 'success'", $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_renders_explicit_danger_alert(): void
    {
        $rendered = Blade::render('<x-alert type="danger" title="Peringatan Keras" message="Terjadi galat tak terduga." />');

        $this->assertStringContainsString('Peringatan Keras', $rendered);
        $this->assertStringContainsString('Terjadi galat tak terduga.', $rendered);
        $this->assertStringContainsString("variant: 'danger'", $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_renders_explicit_warning_alert(): void
    {
        $rendered = Blade::render('<x-alert type="warning" title="Perhatian" message="Batas waktu pendaftaran hampir berakhir." />');

        $this->assertStringContainsString('Perhatian', $rendered);
        $this->assertStringContainsString('Batas waktu pendaftaran hampir berakhir.', $rendered);
        $this->assertStringContainsString("variant: 'warning'", $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_auto_detects_session_success(): void
    {
        session()->flash('success', 'Pendaftaran kontingen berhasil diverifikasi.');

        $rendered = Blade::render('<x-alert />');

        $this->assertStringContainsString('Berhasil Disimpan', $rendered);
        $this->assertStringContainsString('Pendaftaran kontingen berhasil diverifikasi.', $rendered);
        $this->assertStringContainsString("variant: 'success'", $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_auto_detects_session_error(): void
    {
        session()->flash('error', 'Token QR tidak valid atau kedaluwarsa.');

        $rendered = Blade::render('<x-alert />');

        $this->assertStringContainsString('Terjadi Kesalahan', $rendered);
        $this->assertStringContainsString('Token QR tidak valid atau kedaluwarsa.', $rendered);
        $this->assertStringContainsString("variant: 'danger'", $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_auto_detects_session_warning(): void
    {
        session()->flash('warning', 'Berkas bindamping belum lengkap.');

        $rendered = Blade::render('<x-alert />');

        $this->assertStringContainsString('Peringatan', $rendered);
        $this->assertStringContainsString('Berkas bindamping belum lengkap.', $rendered);
        $this->assertStringContainsString("variant: 'warning'", $rendered);
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
        $this->assertStringContainsString("variant: 'danger'", $rendered);
        $this->assertStringContainsString('data-alert-toast', $rendered);
        $this->assertStringNotContainsString('data-alert-root', $rendered);
    }

    public function test_renders_nothing_when_there_is_no_message(): void
    {
        $rendered = Blade::render('<x-alert />');

        $this->assertStringNotContainsString('toast-show', $rendered);
    }
}
