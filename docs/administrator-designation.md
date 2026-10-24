# Administrator Dipilih dari Panitia

## Konsep
- Administrator bukan akun terpisah: akun **Panitia** yang ditunjuk Superadmin (peran `committee` + `admin`).
- Penunjukan lewat menu Aparatur (centang **Jadikan Administrator**), hanya terlihat oleh Superadmin dan hanya untuk akun Panitia. Administrator boleh merangkap Juri.
- `User::isAdministrator()` = penunjukan eksplisit (tanpa pewarisan Superadmin); `User::isAdminOnly()` = Administrator yang tidak merangkap Juri (hanya memantau, tidak menilai).

## Pencatatan
- **Audit log** tetap atas nama akun. Kolom `audit_logs.acting_role` bernilai `admin` bila tindakan dilakukan dengan status Administrator (badge "Administrator" di halaman audit) dan `judge` untuk penilaian juri.
- **Diverifikasi Oleh** pada bukti registrasi dan detail gugus depan ditulis generik "Administrator" beserta tanggal; `scout_groups.verified_by` tidak lagi diisi.
- Penulis pengumuman dan pembuat kode undangan tidak diisi untuk Administrator (`User::recordableId()`).
- Migrasi `2026_10_24_000002_scrub_administrator_identity` mengosongkan data lama tersebut (log audit tidak diubah).

## Seeder
`AdministratorSeeder`: `superadmin` (Superadmin) dan `admin` (Panitia + Administrator), kata sandi `password123`. Dapat dijalankan ulang: `php artisan db:seed --class=AdministratorSeeder` melengkapi peran akun `admin` lama.

## Database yang sudah berjalan (tanpa `migrate:fresh`)
Migrasi `create_audit_logs_table` diubah langsung, jadi tambahkan kolom secara manual:

```sql
ALTER TABLE audit_logs ADD COLUMN acting_role VARCHAR(30) NULL AFTER user_agent;
```

Lalu `php artisan migrate` (menjalankan migrasi data di atas) dan `php artisan db:seed --class=AdministratorSeeder`.
