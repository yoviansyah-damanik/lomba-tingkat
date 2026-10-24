# Banyak Regu per Kategori & Bindamping per Regu

## Konsep
- Gugus depan boleh mengirim lebih dari 1 regu putra dan/atau putri (tanpa batas jumlah). Nama regu unik per gudep (divalidasi di aplikasi).
- Bindamping tidak dibatasi 1 per kategori. Setiap regu wajib didampingi bindamping sekategori; satu bindamping dapat mendampingi beberapa regu.
- Penetapan bindamping ke regu dilakukan saat **mendaftarkan/mengubah bindamping** (bagian "Regu yang Didampingi" pada formulir), bukan saat membentuk regu. Halaman Regu hanya menampilkan bindamping pendamping.
- Syarat pengajuan: tiap kategori yang memiliki regu punya minimal 1 bindamping sekategori, dan setiap regu telah ditetapkan bindampingnya.

## Skema
Kolom `patrols.chaperone_id` (uuid, nullable, FK ke `chaperones`, `ON DELETE SET NULL`) ada pada migrasi `2026_10_18_000006_create_contingents_tables.php`. Untuk database berjalan:

```sql
ALTER TABLE patrols ADD COLUMN chaperone_id uuid NULL;
ALTER TABLE patrols ADD CONSTRAINT patrols_chaperone_id_foreign FOREIGN KEY (chaperone_id) REFERENCES chaperones(id) ON DELETE SET NULL;
```

Migrasi data `2026_10_24_120000_backfill_patrol_chaperones.php` mengisi regu lama dengan bindamping sekategori bila gudep hanya punya satu.

## Kode terkait
- `ScoutGroup::syncChaperonePatrols()` menyinkronkan regu yang didampingi; `missingChaperoneGenders()` dan `rosterProblemsFor()` memuat aturan pengajuan.
- Form-01/02, Penilaian Regu, dasbor dan presensi bindamping memakai regu yang didampingi (`Patrol::chaperone`, `Chaperone::patrols`).
