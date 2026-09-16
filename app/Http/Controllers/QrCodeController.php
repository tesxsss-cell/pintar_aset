<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class QrCodeController extends Controller
{
    public function svg(Asset $asset): Response
    {
        abort_unless($asset->active || auth()->check(), 404);
        $result = (new SvgWriter())->write($this->makeQrCode($asset, 520, 18));

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function png(Request $request, Asset $asset): Response
    {
        $requested = $request->integer('pixels', 1200);
        $pixels = in_array($requested, [800, 1200, 1600], true) ? $requested : 1200;
        $png = $this->makeLabelPng($asset, $pixels);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => (string) strlen($png),
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'attachment; filename="label-'.$this->fileName($asset).'-'.$pixels.'px.png"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function makeLabelPng(Asset $asset, int $pixels): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi PHP GD harus aktif untuk membuat PNG.');
        }

        $scale = $pixels / 1200;
        $canvas = imagecreatetruecolor($pixels, $pixels);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $ink = imagecolorallocate($canvas, 15, 23, 42);
        $muted = imagecolorallocate($canvas, 100, 116, 139);
        $blue = imagecolorallocate($canvas, 37, 99, 235);
        imagefill($canvas, 0, 0, $white);

        imagesetthickness($canvas, max(2, (int) round(4 * $scale)));
        imagerectangle($canvas, 2, 2, $pixels - 3, $pixels - 3, $ink);

        imagefilledrectangle($canvas, (int) (78*$scale), (int) (62*$scale), (int) (154*$scale), (int) (138*$scale), $blue);
        $bold = resource_path('fonts/LiberationSans-Bold.ttf');
        $regular = resource_path('fonts/LiberationSans-Regular.ttf');
        $this->assertFontsExist($bold, $regular);
        $this->drawCenteredText($canvas, 'PA', (int) (28*$scale), (int) (116*$scale), $white, $bold, (int) (78*$scale), (int) (154*$scale));
        imagettftext($canvas, 28*$scale, 0, (int) (180*$scale), (int) (116*$scale), $ink, $bold, 'PINTAR ASET');

        $qrResult = (new PngWriter())->write($this->makeQrCode($asset, (int) (720*$scale), (int) (22*$scale)));
        $qrImage = imagecreatefromstring($qrResult->getString());
        $qrSize = (int) (720*$scale);
        imagecopyresampled($canvas, $qrImage, (int) (240*$scale), (int) (178*$scale), 0, 0, $qrSize, $qrSize, imagesx($qrImage), imagesy($qrImage));

        $this->drawCenteredText($canvas, $asset->code, (int) (24*$scale), (int) (955*$scale), $muted, $bold, 60, $pixels-60);
        $this->drawCenteredText($canvas, $asset->name, (int) (38*$scale), (int) (1025*$scale), $ink, $bold, 70, $pixels-70, (int) (26*$scale));
        $this->drawCenteredText($canvas, $asset->location, (int) (24*$scale), (int) (1080*$scale), $muted, $regular, 70, $pixels-70, (int) (18*$scale));
        $this->drawCenteredText($canvas, 'Pindai untuk melihat informasi aset', (int) (19*$scale), (int) (1145*$scale), $muted, $regular, 70, $pixels-70);

        ob_start();
        imagepng($canvas, null, 9);
        $png = ob_get_clean();
        imagedestroy($qrImage);
        imagedestroy($canvas);

        if (! is_string($png)) {
            throw new RuntimeException('Gagal membuat label PNG.');
        }

        return $png;
    }

    private function drawCenteredText($image, string $text, int $fontSize, int $baseline, int $color, string $font, int $left, int $right, int $minimumSize = 12): void
    {
        do {
            $box = imagettfbbox($fontSize, 0, $font, $text);
            $width = $box[2] - $box[0];
            if ($width <= ($right - $left)) break;
            $fontSize--;
        } while ($fontSize > $minimumSize);

        $x = $left + (int) ((($right - $left) - $width) / 2);
        imagettftext($image, $fontSize, 0, $x, $baseline, $color, $font, $text);
    }

    private function assertFontsExist(string ...$fonts): void
    {
        foreach ($fonts as $font) {
            if (! is_file($font)) throw new RuntimeException('Font label PNG tidak ditemukan: '.$font);
        }
    }

    private function makeQrCode(Asset $asset, int $size, int $margin): QrCode
    {
        return new QrCode(
            data: route('assets.public', $asset),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(15, 23, 42),
            backgroundColor: new Color(255, 255, 255),
        );
    }

    private function fileName(Asset $asset): string
    {
        return str($asset->code)->slug()->toString();
    }
}
