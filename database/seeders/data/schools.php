<?php

/**
 * Data sekolah/pangkalan resmi yang didaftarkan ke sistem (urut sesuai nomor daftar).
 * Format: [nama sekolah, no. gudep putra, no. gudep putri, kwarran, alamat]
 */
$rows = [
    ['SMP Negeri 1 Padangsidimpuan', '01-053', '01-054', 'Padangsidimpuan Utara', 'Jl. Mesjid Raya Baru No. 3'],
    ['SMP Negeri 2 Padangsidimpuan', '02-183', '02-184', 'Padangsidimpuan Selatan', 'Jl. Ade Irma Suryani'],
    ['SMP Negeri 3 Padangsidimpuan', '01-055', '01-056', 'Padangsidimpuan Utara', 'Jl. K.H.A. Dahlan No. 39'],
    ['SMP Negeri 4 Padangsidimpuan', '01-057', '01-058', 'Padangsidimpuan Utara', 'Jl. Sutan Soripada Mulia No. 42'],
    ['SMP Negeri 5 Padangsidimpuan', '02-185', '02-186', 'Padangsidimpuan Selatan', 'Jl. Perintis Kemerdekaan'],
    ['SMP Negeri 6 Padangsidimpuan', '02-187', '02-188', 'Padangsidimpuan Selatan', 'Jl. Kenanga No. 66'],
    ['SMP Negeri 7 Padangsidimpuan', '06-341', '06-342', 'Padangsidimpuan Angkola Julu', 'Jl. Raya Angkola Julu'],
    ['SMP Negeri 8 Padangsidimpuan', '05-317', '05-318', 'Padangsidimpuan Tenggara', 'Pijorkoling'],
    ['SMP Negeri 9 Padangsidimpuan', '04-283', '04-284', 'Padangsidimpuan Hutaimbaru', 'Jl. Merdeka KM 4,5'],
    ['SMP Negeri 10 Padangsidimpuan', '03-255', '03-256', 'Padangsidimpuan Batunadua', 'Jl. Jend. A.H Nasution Ujunggurap'],
    ['SMP Negeri 11 Padangsidimpuan', '02-189', '02-190', 'Padangsidimpuan Selatan', 'Jl. Air Bersih'],
    ['MTsN 1 Padangsidimpuan', '01-059', '01-060', 'Padangsidimpuan Utara', 'Jl. Sutan Soripada Mulia'],
    ['MTsN 2 Padangsidimpuan', '05-349', '05-350', 'Padangsidimpuan Tenggara', 'Jl. H. T. Rizal Nurdin Km. 6,5 Gg. Pendidikan Psp'],
    ['MTsN 3 Padangsidimpuan', '03-363', '03-364', 'Padangsidimpuan Batunadua', 'Jl. Jend. A.H Nasution Ujunggurap'],
    ['SMPS Sari Putra', '01-067', '01-068', 'Padangsidimpuan Utara', 'Jl. Merdeka'],
    ['MTs YPKS Padangsidimpuan', '01-073', '01-074', 'Padangsidimpuan Utara', 'Jl. Sutan Soripada Mulia'],
    ['SMPS Nurul Ilmi', '02-201', '02-202', 'Padangsidimpuan Selatan', 'Jl. Silandit'],
    ['MTsS NU Padangsidimpuan', '02-205', '02-206', 'Padangsidimpuan Selatan', 'Jl. SM. Raja'],
    ['MTs S Hajjah Amaliah Sari', '03-365', '03-366', 'Padangsidimpuan Batunadua', 'Siaparau'],
    ['SMP IT Darul Hasan', '04-365', '04-366', 'Padangsidimpuan Hutaimbaru', 'Jl. Ompu Huta Tunjul Hutaimbaru'],
    ['SMPS Labuanrasoki', '05-319', '05-320', 'Padangsidimpuan Tenggara', 'Labuan Rasoki'],
    ['MTs S Darul Ikhlas', '05-353', '05-354', 'Padangsidimpuan Tenggara', 'Goti'],
    ['SMP Plus IT Padangsidimpuan', '03-373', '03-374', 'Padangsidimpuan Batunadua', 'Jl. Pardomuan I, Desa Aek Tuhul'],
];

return array_map(fn (array $row) => [
    'school' => $row[0],
    'male' => $row[1],
    'female' => $row[2],
    'kwarran' => $row[3],
    'address' => $row[4],
], $rows);
