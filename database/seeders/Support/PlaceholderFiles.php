<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Pembuat berkas unggahan contoh (foto, scan dokumen, PDF) pada disk public untuk seeder lokal/dev
 */
class PlaceholderFiles
{
    private const FONT = 'vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf';

    /** Palet latar pas foto: [r, g, b] */
    private const PALETTE = [[37, 99, 235], [220, 38, 38], [5, 150, 105], [217, 119, 6], [124, 58, 237], [8, 145, 178]];

    /**
     * Pas foto 3x4 berinisial nama pada latar berwarna; mengembalikan path relatif pada disk public
     */
    public static function photo(string $directory, string $name, int $seed = 0): string
    {
        $image = imagecreatetruecolor(300, 400);
        [$r, $g, $b] = self::PALETTE[$seed % count(self::PALETTE)];
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));

        $white = imagecolorallocate($image, 255, 255, 255);
        $shade = imagecolorallocate($image, max($r - 40, 0), max($g - 40, 0), max($b - 40, 0));

        // Siluet kepala dan bahu sederhana
        imagefilledellipse($image, 150, 150, 130, 130, $white);
        imagefilledellipse($image, 150, 380, 260, 220, $white);
        imagefilledellipse($image, 150, 150, 118, 118, $shade);

        self::text($image, self::initials($name), 150, 168, 38, $white, true);

        return self::store($image, $directory, 'foto', 80);
    }

    /**
     * Gambar scan dokumen bergaris tepi dengan judul dan nama pemilik
     */
    public static function scan(string $directory, string $title, string $owner, string $prefix): string
    {
        $image = imagecreatetruecolor(480, 680);
        imagefill($image, 0, 0, imagecolorallocate($image, 250, 250, 247));

        $ink = imagecolorallocate($image, 39, 39, 42);
        $muted = imagecolorallocate($image, 161, 161, 170);
        imagerectangle($image, 18, 18, 461, 661, $muted);
        imagerectangle($image, 24, 24, 455, 655, $muted);

        self::text($image, 'CONTOH BERKAS UNGGAHAN', 240, 80, 13, $muted, true);
        self::text($image, mb_strtoupper($title), 240, 150, 16, $ink, true);
        self::text($image, $owner, 240, 190, 12, $ink, false);

        // Baris teks tiruan agar tampak seperti dokumen
        for ($y = 260; $y < 600; $y += 26) {
            imagefilledrectangle($image, 50, $y, 430 - (($y * 7) % 90), $y + 8, imagecolorallocate($image, 228, 228, 231));
        }

        return self::store($image, $directory, $prefix, 60);
    }

    /**
     * Berkas PDF satu halaman berjudul dan berisi nama pemilik; mengembalikan path relatif pada disk public.
     * Dibuat langsung sebagai PDF minimal (tanpa mesin render) agar ribuan berkas contoh tetap cepat dibuat.
     */
    public static function pdf(string $directory, string $title, string $owner, string $prefix): string
    {
        $path = trim($directory, '/').'/'.$prefix.'_'.bin2hex(random_bytes(6)).'.pdf';
        Storage::disk('public')->put($path, self::minimalPdf(mb_strtoupper($title), $owner));

        return $path;
    }

    private static function minimalPdf(string $title, string $owner): string
    {
        $clean = fn (string $s) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s));

        $content = "BT /F1 18 Tf 72 740 Td ({$clean($title)}) Tj ET\n"
            ."BT /F1 12 Tf 72 710 Td ({$clean($owner)}) Tj ET\n"
            ."BT /F1 10 Tf 72 670 Td (Contoh berkas unggahan - data seeder) Tj ET";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
    private static function store($image, string $directory, string $prefix, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);
        $data = (string) ob_get_clean();

        $path = trim($directory, '/').'/'.$prefix.'_'.bin2hex(random_bytes(6)).'.jpg';
        Storage::disk('public')->put($path, $data);

        return $path;
    }

    private static function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim(preg_replace('/^Kak\s+/i', '', $name)));

        return mb_strtoupper(mb_substr($words[0] ?? '?', 0, 1).mb_substr($words[1] ?? '', 0, 1));
    }

    /**
     * Teks rata tengah pada koordinat x; memakai font TTF bila tersedia, bila tidak font bawaan GD
     */
    private static function text($image, string $text, int $centerX, int $baseline, int $size, int $color, bool $bold): void
    {
        $font = base_path(self::FONT);

        if (is_file($font) && function_exists('imagettftext')) {
            $box = imagettfbbox($size, 0, $font, $text);
            imagettftext($image, $size, 0, (int) ($centerX - ($box[2] - $box[0]) / 2), $baseline, $color, $font, $text);

            return;
        }

        imagestring($image, $bold ? 5 : 3, max($centerX - (int) (strlen($text) * 4.5), 4), $baseline - 12, $text, $color);
    }
}
