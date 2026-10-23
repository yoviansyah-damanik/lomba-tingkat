# Format ID & Manajemen Akun Aparatur

## 1. Format ID Unik & Anti-Duplikasi
- **Gudep**: `G-{no_gudep}` (contoh: `G-01123.01124`)
- **Peserta**: `P-{6 digit acak}` (contoh: `P-829104`)
- **Bindamping**: `B-{6 digit acak}` (contoh: `B-194028`)
- **Aparatur**: `A-{6 digit acak}` (contoh: `A-382976`)
- Pengecekan unik bersifat cross-table di seluruh entitas (`users.id_number`, `participants.id_number`, `chaperones.id_number`, `scout_groups.registration_code`).

## 2. Pendaftaran Mandiri Gudep
- Form pendaftaran tidak memerlukan input username & kata sandi manual.
- Sistem menerbitkan ID, username, dan password otomatis.
- Setelah pendaftaran sukses, halaman `register.scout-group.success` menampilkan kredensial akun dan tombol langsung untuk mengganti kata sandi.

## 3. Manajemen Aparatur (Panitia & Dewan Juri)
- **Modal Tambah/Edit**:
  - Field username dan password dihilangkan.
  - Ceklis status aktif dihilangkan (akun otomatis aktif saat dibuat).
  - Password default mengikuti ID resmi aparatur (`A-XXXXXX`).
  - Kolom `must_change_password` otomatis bernilai `true`.
- **Login Pertama**:
  - Pengguna dengan kata sandi awal otomatis diarahkan ke halaman pengaturan keamanan (`/settings/security`) untuk mengganti kata sandi.
  - Setelah kata sandi diperbarui, `must_change_password` berubah menjadi `false` dan akses penuh terbuka.
- **Sinkronisasi & Penghapusan**:
  - Edit peran aparatur langsung menyelaraskan role pada akun pengguna terkait.
  - Hapus aparatur otomatis menghapus akun pengguna beserta berkas pas foto terkait.
