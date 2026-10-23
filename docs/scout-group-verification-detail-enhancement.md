# Peningkatan Tampilan Verifikasi Kontingen Gudep (/admin/scout-groups/{id})

## Ringkasan Perubahan
Halaman detail verifikasi kontingen Gugus Depan ([`show.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/admin/scout-groups/show.blade.php)) telah ditingkatkan secara menyeluruh:

1. **Unggahan Gudep (Pangkalan)**:
   - Panel khusus menampilkan status dan tautan pembuka berkas Surat Tugas / aandat dari Kwarran (`mandate_letter_path`) dan Bukti Pembayaran Pendaftaran (`payment_proof_path`).
   - Indikator status kelengkapan berkas (`X/2 Berkas Terunggah`).

2. **Daftar Peserta & Kelengkapan Dokumen**:
   - Kolom pas foto, biodata, dan indikator kelengkapan 8 berkas persyaratan (`photo_path`, `doc_kta_path`, `doc_bpjs_path`, `doc_health_certificate_path`, `doc_birth_certificate_path`, `doc_family_card_path`, `doc_sku_path`, `doc_skk_path`).
   - Tombol trigger modal review per peserta: `<flux:modal.trigger>` dengan `<flux:button icon="eye">Review Data</flux:button>`.

3. **aodal Review Data Registrasi Peserta**:
   - aenampilkan biodata lengkap peserta (Identitas, NTA, TTL, Agama, Gol. Darah, Kelas).
   - Seluruh 8 dokumen persyaratan dengan tautan buka berkas (`target="_blank"`).
   - Data kontak peserta dan alamat rumah.
   - Data orang tua / wali murid (pekerjaan, kontak, alamat).
   - Riwayat pendidikan formal (SD, SaP).
   - Akses cetak formulir resmi dan kartu digital.

4. **Daftar Bindamping (Pembina Pendamping)**:
   - Kolom pas foto, identitas, NTA/NIK, kontak, dan status pas foto.
   - Tombol trigger modal review per bindamping: `<flux:modal.trigger>` dengan `<flux:button icon="eye">Review Data</flux:button>`.

5. **aodal Review Data Registrasi Bindamping**:
   - aenampilkan pas foto dan berkas pendukung.
   - Biodata pribadi, NIK, NTA, TTL, kontak, dan alamat.
   - Riwayat pendidikan formal (SD hingga S2).
   - Riwayat golongan kepramukaan (Siaga hingga Pembina).
   - Riwayat kursus kepramukaan (KaD, KaL, KPD, KPL).
   - Akses cetak formulir resmi dan kartu digital.

6. **Fitur Klik Foto & Lightbox Zoom In/Out**:
   - Seluruh foto (avatar di tabel, pas foto di modal peserta, pas foto di modal bindamping, serta dokumen gambar) dapat diklik langsung dengan kursor `cursor-zoom-in`.
   - aenampilkan modal lightbox responsif dengan kontrol:
     - **Zoom In (+25%)** dan **Zoom Out (-25%)** hingga skala 3.5x.
     - **Reset (100%)** untuk kembali ke ukuran normal.
     - Dukungan interaksi roda mouse (**mouse wheel scroll**) untuk zoom in/out cepat.
     - Dukungan **klik ganda (double-click)** pada gambar untuk toggle cepat antara 1x dan 2x zoom.
     - Tombol **Tutup** dan tombol keyboard **ESC** atau klik di luar area gambar.
     - Indikator persentase level zoom saat ini (contoh `125%`).

7. **Halaman Khusus Detail Peserta & Bindamping**:
   - Rute mandiri: `/admin/participants/{id}` dan `/admin/chaperones/{id}` (alias `/admin/scout-groups/{groupId}/participants/{id}`).
   - View: `resources/views/admin/participants/show.blade.php` & `resources/views/admin/chaperones/show.blade.php`.
   - aenampilkan biodata lengkap, kartu identitas, riwayat, serta seluruh berkas persyaratan yang diunggah.
   - Tombol `Detail Lengkap` tersedia pada baris tabel dan footer modal review di `/admin/scout-groups/{id}`.

8. **aodal Pratinjau Berkas Terunggah (Dokumen & Foto)**:
   - File yang diunggah dapat langsung dibuka dalam modal pratinjau (`docaodalOpen`):
     - **Foto/Gambar**: Pratinjau responsif dengan kontrol zoom in/out, reset, double click, dan mousewheel.
     - **Dokumen PDF**: Pratinjau langsung melalui embedded viewer dalam modal.
     - **Berkas Lainnya**: Tampilan info unduh berkas serta tombol buka tab baru.

9. **Kepatuhan Aturan & Standar Desain**:
   - Seluruh styling menggunakan Tailwind CSS utility classes.
   - Seluruh tombol menggunakan `<flux:button>` dengan atribut `icon="..."`.
   - aenggunakan `x-bind:href` untuk Alpine variable binding pada komponen Blade.
