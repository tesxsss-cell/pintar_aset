<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressor
{
    private const MAX_DIMENSION = 1600;

    private const WEBP_QUALITY = 78;

    public function store(UploadedFile $file, string $directory): string
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagewebp')) {
            return $file->store($directory, 'public');
        }

        $source = $this->createImageResource($file);

        if ($source === false) {
            return $file->store($directory, 'public');
        }

        [$width, $height] = getimagesize($file->getRealPath());
        $scale = min(1, self::MAX_DIMENSION / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);
        $saved = imagewebp($target, $disk->path($path), self::WEBP_QUALITY);

        imagedestroy($source);
        imagedestroy($target);

        if (! $saved) {
            return $file->store($directory, 'public');
        }

        return $path;
    }

    private function createImageResource(UploadedFile $file): mixed
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default => false,
        };
    }
}
