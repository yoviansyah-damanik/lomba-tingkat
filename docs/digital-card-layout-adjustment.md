# Penyesuaian Tata Letak Kartu Digital: Pas Foto, Nama, NTA & ID

## Ringkasan
Menyatukan dan merapikan hierarki visual identitas pada kartu tanda pengenal digital resmi B2 sehingga tersusun berurutan:
1. **Pas Foto** ([photo.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/photo.blade.php))
2. **Nama Lengkap** ([meta-info.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/meta-info.blade.php))
3. **NTA dan Nomor ID** ([meta-info.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/meta-info.blade.php))

## Rincian Perubahan
- **Komponen `meta-info.blade.php`**:
  - Membungkus Nama dan NTA/ID ke dalam container flex mandiri `w-full text-center shrink-0 my-0.5` agar tidak renggang oleh `justify-between` pada body kartu.
  - Nama ditampilkan tegas (`text-[9.5pt] font-black uppercase text-slate-900`) tepat di bawah pas foto.
  - NTA dan nomor ID ditampilkan tepat di bawah nama dengan format monospace bersih (`NTA: [nomor] • [ID-XXXX]`).
- **Pembersihan Kartu Bindamping (`chaperone.blade.php`)**:
  - Menghilangkan duplikasi baris tabel "Pangkalan" yang sempat muncul dua kali.
