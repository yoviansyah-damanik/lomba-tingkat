# Digital Cards Component & Tailwind Refactoring

## Ringkasan
Seluruh berkas tampilan digital card telah dibersihkan dari blok `<style>` CSS manual dan dialihkan ke komponen reusable berstandar Tailwind CSS utility classes.

## Komponen Reusable (`resources/views/components/digital-card/`)
- `layout.blade.php`: Shell HTML dengan asset Vite (`@vite`) dan aturan printer minimal `@page` untuk ukuran B2.
- `card.blade.php`: Kontainer kartu ukuran resmi B2 (79 x 126 mm) dengan watermark logo.
- `header.blade.php`: Bagian atas kartu dengan gradient tema, logo, dan judul.
- `category-strip.blade.php`: Pita kategori bertema.
- `body.blade.php`: Kontainer flex badan kartu.
- `photo.blade.php`: Pas foto terstandarisasi untuk Anggota, Pinru, Wapinru, Bindamping, dan Panitia.
- `meta-info.blade.php`: Nama peserta/aparatur, NTA, dan nomor ID resmi tanpa box.
- `details-table.blade.php` & `details-row.blade.php`: Tabel dan baris rincian data.
- `qr-section.blade.php`: Bagian verifikasi QR code dengan caption resmi dan token.
- `footer.blade.php`: Footer bawah kartu dengan background bertema.
- `toolbar.blade.php`: Toolbar aksi cetak dan kembali responsif.

## Berkas yang Direfaktor
- `resources/views/digital-cards/participant.blade.php`
- `resources/views/digital-cards/chaperone.blade.php`
- `resources/views/digital-cards/staff.blade.php`
- `resources/views/digital-cards/verify.blade.php`
- `resources/views/digital-cards/patrol-batch.blade.php`
- `resources/views/digital-cards/chaperone-batch.blade.php`

Semua pengujian (`php artisan test`) berhasil 100% (137 passed, 5 skipped).
