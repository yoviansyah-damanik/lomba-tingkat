# Penyematan App URL pada QR Code Section ID Card

## Ringkasan
Menyematkan konfigurasi `APP_URL` (`config('app.url')`) ke dalam komponen kartu digital [qr-section.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/qr-section.blade.php).

## Rincian Perubahan
1. **Komponen Blade (`qr-section.blade.php`)**:
   - Menambahkan prop `$appUrl = null` yang secara default me-resolve `config('app.url')`.
   - Menampilkan teks mono URL di bawah token QR:
     ```blade
     @if ($resolvedAppUrl)
         <span class="block text-[4.8pt] text-slate-400 font-mono tracking-tight mt-[1px] truncate">{{ $resolvedAppUrl }}</span>
     @endif
     ```
2. **Otomatis Aktif di Seluruh Kartu**:
   - Kartu Peserta ([participant.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/participant.blade.php) & [patrol-batch.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/patrol-batch.blade.php))
   - Kartu Bindamping ([chaperone.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/chaperone.blade.php) & [chaperone-batch.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/chaperone-batch.blade.php))
   - Kartu Panitia / Juri ([staff.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/staff.blade.php))
3. **Data QR Code**:
   - `route('digital-cards.verify', $token)` pada [DigitalCardController.php](file:///d:/WebApps/lomba-tingkat/app/Http/Controllers/DigitalCardController.php) menggunakan URL aplikasi lengkap untuk validasi pemindaian langsung.
