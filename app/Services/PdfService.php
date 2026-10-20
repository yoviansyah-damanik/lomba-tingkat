<?php

namespace App\Services;

use Dompdf\Canvas;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Illuminate\Http\Response;

/**
 * Pembuatan dokumen PDF A4 dari view Blade (dipakai seluruh laporan cetak)
 */
class PdfService
{
    /**
     * Teks footer kiri seluruh laporan
     */
    public const FOOTER_LEFT = 'Lembar ini merupakan bagian dari berkas resmi penyelenggaraan Lomba Tingkat III Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan.';

    private const FOOTER_FONT_SIZE = 7;

    private const FOOTER_MARGIN = 42.5; // 1,5 cm sesuai @page

    /**
     * Render view menjadi isi berkas PDF
     */
    public function render(string $view, array $data = [], string $orientation = 'portrait'): string
    {
        $dompdf = new Dompdf((new Options)
            ->set('isRemoteEnabled', false)
            ->set('chroot', base_path())
            ->set('defaultFont', 'DejaVu Sans'));

        $dompdf->loadHtml(view($view, $data)->render());
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        $this->drawFooter($dompdf, now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB');

        return $dompdf->output();
    }

    /**
     * Respons PDF: tampil langsung (pratinjau) atau diunduh bila $download true
     */
    public function response(string $view, array $data, string $filename, bool $download = false, string $orientation = 'portrait'): Response
    {
        return response($this->render($view, $data, $orientation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"',
        ]);
    }

    /**
     * Footer dua bagian di setiap halaman: kiri keterangan berkas, kanan URL aplikasi
     * lalu baris "{waktu cetak} - Hal n/N"
     */
    private function drawFooter(Dompdf $dompdf, string $printedAt): void
    {
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $size = self::FOOTER_FONT_SIZE;
        $color = [0.443, 0.443, 0.478]; // #71717a

        $left = self::FOOTER_MARGIN;
        $right = $canvas->get_width() - self::FOOTER_MARGIN;
        $baseline = $canvas->get_height() - 30;
        $lineHeight = $size + 3;

        $url = (string) config('app.url');

        // Bagian kanan dibatasi sepertiga lebar; bagian kiri dibungkus pada sisa lebar
        $leftMaxWidth = ($right - $left) * 0.55;
        $leftLines = $this->wrap(self::FOOTER_LEFT, $font, $size, $leftMaxWidth, $fontMetrics);

        $canvas->page_script(function (int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $metrics) use ($font, $size, $color, $left, $right, $baseline, $lineHeight, $leftLines, $url, $printedAt) {
            $canvas->line($left, $baseline - 9, $right, $baseline - 9, [0.831, 0.831, 0.847], 0.5);

            foreach ($leftLines as $i => $line) {
                $canvas->text($left, $baseline + ($i * $lineHeight), $line, $font, $size, $color);
            }

            // Bagian kanan dua baris rata kanan: URL, lalu waktu cetak dan nomor halaman
            foreach ([$url, "{$printedAt} - Hal {$pageNumber}/{$pageCount}"] as $i => $rightText) {
                $width = $metrics->getTextWidth($rightText, $font, $size);
                $canvas->text($right - $width, $baseline + ($i * $lineHeight), $rightText, $font, $size, $color);
            }
        });
    }

    /**
     * Bungkus teks menjadi beberapa baris sesuai lebar maksimum
     *
     * @return array<int, string>
     */
    private function wrap(string $text, string $font, float $size, float $maxWidth, FontMetrics $metrics): array
    {
        $lines = [];
        $current = '';

        foreach (explode(' ', $text) as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if ($current !== '' && $metrics->getTextWidth($candidate, $font, $size) > $maxWidth) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }
}
