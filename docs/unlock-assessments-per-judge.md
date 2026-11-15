# Dokumentasi Buka Finalisasi Penilaian Per Juri

## Ringkasan Fitur
Fitur pembukaan kunci penilaian yang telah difinalisasi agar dapat dibuka kembali menjadi status **Draf Terbuka** secara per juri (bulk) maupun per penilaian individu, baik melalui modul audit pusat maupun langsung pada lembar penilaian juri.

## Perubahan Utama
1. **`AssessmentScoringService::unlockScoresByJudge`**
   - Membuka kunci seluruh penilaian terfinalisasi milik juri tertentu dalam satu transaksi database.
   - Opsional: filter berdasarkan cabang lomba (`competitionId`) atau jadwal sesi (`scheduleId`).
   - Mencatat log audit (`score_unlocked`) untuk setiap penilaian yang dibuka beserta alasan wajib.
   - Mengkalkulasi ulang akumulasi nilai klasemen (`aggregatePatrolScore`).

2. **`AssessmentsAudit` (`/admin/assessments`)**
   - Hak akses disesuaikan untuk peran Administrator dan Superadmin.
   - Filter daftar penilaian berdasarkan Dewan Juri (`judgeFilter`).
   - Kartu ringkasan status penilaian dewan juri (`judgesSummary`) menampilkan jumlah terkunci dan draf.
   - Modal pembukaan finalisasi per juri (`isUnlockJudgeModalOpen`) dengan preview jumlah penilaian terdampak dan input alasan audit wajib.
   - Tombol cepat pada baris tabel untuk membuka penilaian tunggal atau seluruh nilai juri terkait.

3. **`JudgeScoring` (`/portal/judge/scoring/{scheduleId}`)**
   - **Tombol Header**: Tombol *Buka Finalisasi Juri* di header aksi Administrator.
   - **Ringkasan Dewan Juri Sesi Ini**: Panel status seluruh dewan juri yang ditugaskan pada sesi lomba ini (jumlah penilaian terkunci dan draf) beserta tombol aksi cepat *Buka Final*.
   - **Modal Pembukaan Finalisasi Juri pada Sesi Ini**: Memilih dewan juri penilai di sesi terkait, melihat indikator jumlah penilaian terfinalisasi yang terdampak, dan memasukkan alasan pembukaan kunci wajib masuk jejak audit.
   - **Pemilih Juri pada Regu Aktif**: Administrator dapat berpindah antar juri penilai (`selectJudgeForPatrol`) saat memeriksa penilaian satu regu yang dinilai oleh beberapa juri.
   - **Modal Pembukaan Kunci Nilai Regu**: Pilihan cakupan *Hanya Regu Ini* (`single`) atau *Semua Nilai Juri Ini (Sesi Ini)* (`judge_all`).

4. **Pengujian Otomatis**
   - `tests/Feature/AssessmentsAuditTest.php` (8 pengujian, 42 assertions) dan `tests/Feature/JudgeScoringTest.php` (16 pengujian, 75 assertions) lulus 100%.
