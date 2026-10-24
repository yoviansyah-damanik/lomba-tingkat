# Login dengan Username (Tanpa Email)

Akun pengguna tidak lagi memakai email. Login cukup dengan **username** dan **kata sandi**.

## Aturan
- Kolom `users.username` unik, huruf kecil, 3 sampai 50 karakter, hanya huruf, angka, titik, strip, dan garis bawah (tanpa spasi). Aturan ada di `User::usernameRules()`.
- Login tidak membedakan huruf besar/kecil dan mengabaikan spasi di awal/akhir.
- Kolom `email`, `email_verified_at`, dan tabel `password_reset_tokens` dihapus.
- Fitur lupa kata sandi dan verifikasi email dihapus. Kata sandi direset oleh Administrator di menu Manajemen Pengguna.
- Kontak tetap lewat `phone_number`. Email resmi kegiatan (`events.contact_email`) tidak berubah.

## Akun contoh (seeder, kata sandi `password123`)
| Peran | Username |
|---|---|
| Administrator | `admin` |
| Panitia | `panitia` |
| Juri | `juri` |
| Panitia merangkap Juri | `panitia.juri` |
| Gugus Depan | `G-01001.01002` (username = ID Gudep) |
| Peserta | `peserta` |
| Bindamping putra | `bindamping` |
| Bindamping putri | `bindamping.putri` |

## Migrasi database yang sudah berisi data
Migrasi awal (`0001_01_01_000000_create_users_table`) diubah di tempat. Pada server yang sudah berjalan, jangan `migrate:fresh`. Jalankan SQL berikut (MariaDB/MySQL):

```sql
ALTER TABLE users ADD COLUMN username VARCHAR(255) NULL AFTER name;

-- Isi username dari bagian depan email lama; tambahkan akhiran id bila ada duplikat
UPDATE users SET username = LOWER(SUBSTRING_INDEX(email, '@', 1));
UPDATE users u
JOIN (SELECT username, COUNT(*) c FROM users GROUP BY username HAVING c > 1) d ON d.username = u.username
SET u.username = CONCAT(u.username, '.', SUBSTRING(u.id, 1, 4));

ALTER TABLE users MODIFY username VARCHAR(255) NOT NULL, ADD UNIQUE KEY users_username_unique (username);
ALTER TABLE users DROP INDEX users_email_unique, DROP COLUMN email, DROP COLUMN email_verified_at;
DROP TABLE IF EXISTS password_reset_tokens;
```

Setelah itu umumkan username baru kepada pengguna (Administrator dapat melihatnya di Manajemen Pengguna).

## Akun otomatis saat kontingen diverifikasi
Saat Administrator memverifikasi kontingen (halaman detail maupun daftar verifikasi), sistem membuat akun yang belum ada lewat `ContingentAccountService`:

| Akun | Username | Kata sandi awal |
|---|---|---|
| Bindamping | `bindamping.{no_gudep}` (contoh `bindamping.01093`) | tanggal lahir `Ymd` (contoh `19850515`) |
| Peserta | `peserta.{no_gudep}.{no_urut}` (contoh `peserta.01093.01`) | tanggal lahir `Ymd` |

- `no_gudep` adalah nomor gudep tanpa simbol "-" dan mengikuti gender: putra memakai nomor ganjil (`01093`), putri memakai nomor genap (`01094`).
- `no_urut` dua digit, berurutan per gender dalam satu gudep: Pinru, Wapinru, lalu anggota, regu demi regu.
- Akun hanya dibuat saat status Disetujui. Verifikasi ulang tidak menggandakan akun. Bindamping tanpa tanggal lahir dilewati.
- Akun peserta tertaut ke `users.participant_id`, akun bindamping ke `chaperones.user_id`, sehingga langsung dapat membuka halaman Penilaian Regu.
- Sarankan pengguna mengganti kata sandi awal setelah login pertama (menu Pengaturan).
