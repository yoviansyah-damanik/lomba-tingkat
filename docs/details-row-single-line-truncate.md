# Pembatasan Satu Baris & Truncate Otomatis pada Details Row Kartu Digital

## Ringkasan
Menstandarisasi baris informasi rincian kartu tanda pengenal digital B2 agar tetap 1 baris tanpa memecah layout vertikal kartu jika teks data terlalu panjang.

## Rincian Perubahan
1. **[`details-table.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/details-table.blade.php)**:
   - Ditambahkan utilitas `table-fixed` agar lebar kolom strictly patuh pada persentase lebar yang ditentukan.
2. **[`details-row.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/details-row.blade.php)**:
   - Kolom label: Diberikan `whitespace-nowrap w-[38%]`.
   - Kolom pemisah: Diberikan `w-[4%] text-center`.
   - Kolom nilai: Diberikan `w-[58%] max-w-0` dengan pembungkus `<div class="truncate leading-normal" title="{{ $titleText }}">`.
   - Bila teks melampaui lebar kolom, teks otomatis terpotong dengan tanda elipsis (`...`) dan nilai lengkap tetap dapat dibaca melalui atribut `title` saat kursor diarahkan ke elemen tersebut.
