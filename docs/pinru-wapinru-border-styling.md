# Diferensiasi Warna Border & Pita PINRU vs WAPINRU

## Ringkasan
Warna border dan pita vertikal pas foto pada kartu digital (`<x-digital-card.photo>`) telah dibedakan secara kontras agar panitia dan juri pos dapat membedakan peran kepemimpinan regu secara instan:
- **PINRU (Pimpinan Regu)**: Menggunakan warna **Merah** (`border-red-600 bg-red-600`) melambangkan komando utama regu penggalang.
- **WAPINRU (Wakil Pimpinan Regu)**: Menggunakan warna **Biru** (`border-blue-600 bg-blue-600`) sebagai pembeda wakil pemimpin regu (mendukung kustomisasi opsi: `amber`, `emerald`, `indigo` via prop `assistantColor`).
- **Anggota Reguler**: Menggunakan border netral **Slate** (`border-slate-300`).

## Berkas yang Diperbarui
- `resources/views/components/digital-card/photo.blade.php`

Seluruh 148 pengujian (`php artisan test`) berhasil 100% (143 passed, 5 skipped).
