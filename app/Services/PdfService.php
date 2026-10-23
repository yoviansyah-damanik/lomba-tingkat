<?php

namespace App\Services;

use Dompdf\Canvas;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Pembuatan dokumen PDF A4 dari view Blade (dipakai seluruh laporan cetak)
 */
class PdfService
{
    /** Teks footer kiri seluruh laporan */
    public const FOOTER_LEFT = 'Lembar ini merupakan bagian dari berkas resmi penyelenggaraan Lomba Tingkat III Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan.';

    /** Pratinjau dan PDF memakai URL yang sama dengan query berbeda: jangan di-cache agar tidak tertukar */
    private const VARY_HEADERS = [
        'Cache-Control' => 'no-store, private',
    ];

    private const FOOTER_FONT_SIZE = 7;

    /**
     * Margin kiri/kanan footer dalam pt: 15mm = 42.52pt
     * Sesuai @page { margin: top right(15mm) bottom left(15mm) } di seluruh template PDF.
     */
    private const FOOTER_MARGIN_LR = 42.52;

    /**
     * Offset baseline footer dari tepi bawah halaman dalam pt.
     *
     * Kalkulasi (A4 portrait, Y=0 di atas, bertambah ke bawah):
     *   Page height         = 841.89 pt
     *   @page margin-bottom = 28mm = 79.37 pt
     *   Batas bawah konten  = 841.89 - 79.37 = 762.52 pt
     *
     *   OFFSET = 52 → baseline = 841.89 - 52 = 789.89 pt
     *   Garis  = 789.89 - 8    = 781.89 pt  (di bawah konten 762pt ✓)
     *   Baris1 = 789.89 pt
     *   Baris2 = 789.89 + 9.5 = 799.39 pt  (dalam halaman 841pt ✓)
     */
    private const FOOTER_BASELINE_OFFSET = 52;

    /**
     * Path logo versi kecil (256px) untuk kop PDF; dibuat sekali lalu dipakai ulang
     * agar dompdf tidak mendekode logo resolusi penuh pada setiap dokumen.
     */
    public static function logoPath(): string
    {
        $source = public_path('logo.png');
        $cached = storage_path('app/pdf-cache/logo-256.png');

        if (is_file($cached) && filemtime($cached) >= filemtime($source)) {
            return $cached;
        }

        if (! function_exists('imagecreatefrompng')) {
            return $source;
        }

        $image = @imagecreatefrompng($source);
        if (! $image) {
            return $source;
        }

        $small = imagescale($image, 256, -1, IMG_BICUBIC);
        imagealphablending($small, false);
        imagesavealpha($small, true);

        if (! is_dir(dirname($cached))) {
            @mkdir(dirname($cached), 0755, true);
        }
        imagepng($small, $cached, 9);

        return $cached;
    }

    /**
     * Render view menjadi isi berkas PDF
     */
    public function render(string $view, array $data = [], string $orientation = 'portrait'): string
    {
        $dompdf = new Dompdf((new Options)
            ->set('isRemoteEnabled', false)
            ->set('chroot', base_path())
            ->set('isFontSubsettingEnabled', true)
            ->set('defaultFont', 'DejaVu Sans'));

        $dompdf->loadHtml(view($view, $data)->render());
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        $printedAt = now()->timezone('Asia/Jakarta');
        $this->drawFooter($dompdf, $printedAt->format('d/m/Y H:i:s').' WIB', $this->uniqueNumber($printedAt));

        return $dompdf->output();
    }

    /**
     * Respons PDF: tampil langsung (pratinjau) atau diunduh bila $download true
     */
    public function response(string $view, array $data, string $filename, bool $download = false, string $orientation = 'portrait'): Response
    {
        if ($this->wantsPreviewPage($download)) {
            return response(view('reports.pdf-preview', [
                'title' => str_replace('_', ' ', pathinfo($filename, PATHINFO_FILENAME)),
                'pdfUrl' => request()->fullUrlWithQuery(['raw' => 1]),
                'downloadUrl' => request()->fullUrlWithQuery(['download' => 1, 'raw' => null]),
                'backUrl' => null,
            ])->render(), 200, self::VARY_HEADERS);
        }

        return response($this->render($view, $data, $orientation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"',
        ] + self::VARY_HEADERS);
    }

    /**
     * Halaman pratinjau menjadi tampilan bawaan; PDF mentah hanya untuk ?raw=1 (isi iframe) dan ?download=1
     */
    private function wantsPreviewPage(bool $download): bool
    {
        return ! $download && ! request()->boolean('raw');
    }

    /**
     * Nomor unik setiap hasil cetak: No. LT3-{tanggal-jam cetak}-{kode acak}
     */
    private function uniqueNumber(\DateTimeInterface $printedAt): string
    {
        return 'No. LT3-'.$printedAt->format('ymd-His').'-'.strtoupper(Str::random(5));
    }

    /**
     * Footer dua kolom di setiap halaman:
     * - Kiri: keterangan berkas resmi (2 baris)
     * - Kanan: URL aplikasi, waktu cetak & nomor halaman, lalu nomor unik cetak
     * Diawali garis tipis pemisah.
     */
    private function drawFooter(Dompdf $dompdf, string $printedAt, string $uniqueNumber): void
    {
        $canvas      = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font        = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $size        = self::FOOTER_FONT_SIZE;
        $color       = [0.443, 0.443, 0.478]; // #71717a

        $left     = self::FOOTER_MARGIN_LR;
        $right    = $canvas->get_width() - self::FOOTER_MARGIN_LR;
        $baseline = $canvas->get_height() - self::FOOTER_BASELINE_OFFSET;
        $lineH    = $size + 2.5;

        $url = (string) config('app.url');

        // Kolom kiri dibungkus pada 55% lebar area cetak
        $leftMaxWidth = ($right - $left) * 0.55;
        $leftLines    = $this->wrap(self::FOOTER_LEFT, $font, $size, $leftMaxWidth, $fontMetrics);

        $canvas->page_script(function (int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $metrics) use (
            $font, $size, $color, $left, $right, $baseline, $lineH, $leftLines, $url, $printedAt, $uniqueNumber
        ) {
            // Garis pemisah tipis
            $canvas->line($left, $baseline - 8, $right, $baseline - 8, [0.831, 0.831, 0.847], 0.5);

            // Teks kiri (2 baris keterangan)
            foreach ($leftLines as $i => $line) {
                $canvas->text($left, $baseline + ($i * $lineH), $line, $font, $size, $color);
            }

            // Teks kanan: URL baris 1, waktu + halaman baris 2, nomor unik baris 3
            foreach ([$url, "{$printedAt} - Hal {$pageNumber}/{$pageCount}", $uniqueNumber] as $i => $rightText) {
                $width = $metrics->getTextWidth($rightText, $font, $size);
                $canvas->text($right - $width, $baseline + ($i * $lineH), $rightText, $font, $size, $color);
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
        $lines   = [];
        $current = '';

        foreach (explode(' ', $text) as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if ($current !== '' && $metrics->getTextWidth($candidate, $font, $size) > $maxWidth) {
                $lines[]  = $current;
                $current  = $word;
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
