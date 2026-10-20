# Pembaruan ID Card & Penghapusan NIK Pembina Pendamping

## 1. Penghapusan NIK untuk Pembina Pendamping (Bindamping)
- **Konteks**: Berdasarkan regulasi dan kebutuhan terbaru, Pembina Pendamping tidak lagi memerlukan penginputan atau penampilan NIK.
- **Perubahan Tampilan & Form**:
  - `resources/views/portal/scout-group/chaperones/create.blade.php`: Field NIK (KTP) dihapus dari form pendaftaran.
  - `resources/views/portal/scout-group/chaperones/edit.blade.php`: Field NIK (KTP) dihapus dari form edit.
  - `resources/views/portal/scout-group/chaperones/index.blade.php` & `livewire/portal/chaperones-management.blade.php`: Penampilan NIK pada kartu pembina ditiadakan.
  - `resources/views/portal/scout-group/chaperones/print.blade.php`: Baris NIK pada formulir cetak ditiadakan.
  - `resources/views/livewire/admin/chaperones-directory.blade.php`: NIK dihapus dari placeholder pencarian, baris tabel, dan modal detail biodata.
  - `resources/views/admin/scout-groups/show.blade.php`: Kolom NIK pada tabel bindamping dihapus.
  - `app/Livewire/Admin/ChaperonesDirectory.php`: Kueri pencarian disesuaikan tanpa NIK.

## 2. Standardisasi ID Card Digital (B2) & Penyesuaian NTA / Kontak
- **Seluruh Template ID Card Diperbarui**:
  - [participant.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/participant.blade.php) (Satuan Peserta)
  - [patrol-batch.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/patrol-batch.blade.php) (Kolektif Regu)
  - [chaperone.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/chaperone.blade.php) (Satuan Bindamping)
  - [chaperone-batch.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/chaperone-batch.blade.php) (Kolektif Bindamping)
  - [staff.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/staff.blade.php) (Panitia & Dewan Juri)
- **Ketentuan Desain & Tata Letak**:
  - **Nomor ID & NTA**: Seluruh kartu menampilkan format tunggal seragam tepat di bawah nama:
    `NTA: [Nomor NTA] • [Nomor ID]`
    (tanpa kotak/border background dan tanpa teks role tambahan seperti `PANITIA` / `PEMBINA PENDAMPING` / `DEWAN JURI` di samping ID).
  - **Eliminasi Duplikasi NTA**: Baris NTA di dalam tabel rincian bawah (`details-grid`) telah dihapus pada kartu pendamping dan aparatur karena sudah terpampang jelas di bawah nama.
  - **Kontak / WhatsApp Peserta**: Ditambahkan baris `Kontak/WA` pada tabel informasi kartu peserta satuan (`participant.blade.php`) dan kolektif regu (`patrol-batch.blade.php`).

## 3. Penyesuaian Nomor Gudep Spesifik Putra / Putri untuk Bindamping
- **Model `Chaperone`**: Ditambahkan accessor `getGroupNumberAttribute()` pada [Chaperone.php](file:///d:/WebApps/lomba-tingkat/app/Models/Chaperone.php) yang secara otomatis mengembalikan nomor ganjil (`male_group_number`) untuk Bindamping Putra dan nomor genap (`female_group_number`) untuk Bindamping Putri.
- **Implementasi Tampilan & Verifikasi QR**:
  - [chaperone.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/chaperone.blade.php) & [chaperone-batch.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/chaperone-batch.blade.php): Menggunakan `$chaperone->group_number`.
  - [QrCodeService.php](file:///d:/WebApps/lomba-tingkat/app/Services/QrCodeService.php): Data QR code Bindamping menyertakan nomor Gudep spesifik gender.
  - [chaperones-directory.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/chaperones-directory.blade.php): Tabel dan modal detail admin menampilkan nomor Gudep spesifik pembina putra/putri.

## 4. Penyeragaman Keterangan QR Code & Nomor Token Penuh
- **Format Keterangan QR Code**: Diperbarui pada seluruh 5 ID Card menjadi:
  `Kartu digital resmi {{ config('app.name') }} {{ config('app.description') }}.`
- **Nomor Token Lengkap**: Tidak lagi dipotong (`substr`), seluruh karakter token ditampilkan secara penuh dengan styling `word-break: break-all` yang responsif.
