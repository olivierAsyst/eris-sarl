<?php

namespace App\Image;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Filter\Basic\Autorotate;

/**
 * Réduit une image trop grande "sur place" (les photos d'appareil font souvent 4000 px / 5 Mo).
 * Les miniatures LiipImagine sont ensuite générées à partir de ce fichier allégé.
 */
final class ImageOptimizer
{
    public const MAX_SIZE = 2400;

    /**
     * @return bool true si le fichier a été réduit
     */
    public function shrink(string $path, int $maxSize = self::MAX_SIZE, int $quality = 82): bool
    {
        if (!is_file($path)) {
            return false;
        }
        $info = @getimagesize($path);
        if (false === $info || !\in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return false;
        }
        [$width, $height] = $info;
        $tooBig = $width > $maxSize || $height > $maxSize;
        $tooHeavy = filesize($path) > 1_500_000;
        if (!$tooBig && !$tooHeavy) {
            return false;
        }

        self::raiseMemoryLimit();

        $imagine = new Imagine();
        $image = $imagine->open($path);
        // Remet les photos de téléphone à l'endroit (orientation EXIF)
        $image = (new Autorotate())->apply($image);

        if ($tooBig) {
            $image = $image->thumbnail(new Box($maxSize, $maxSize), ImageInterface::THUMBNAIL_INSET);
        }

        $image->save($path, [
            'jpeg_quality' => $quality,
            'png_compression_level' => 8,
            'webp_quality' => $quality,
        ]);
        clearstatcache(true, $path);

        return true;
    }

    /** GD a besoin d'environ 5 octets par pixel : 128 Mo ne suffisent pas pour une photo de 12 Mpx. */
    public static function raiseMemoryLimit(string $limit = '512M'): void
    {
        $current = ini_get('memory_limit');
        if ('-1' !== $current && self::toBytes((string) $current) < self::toBytes($limit)) {
            @ini_set('memory_limit', $limit);
        }
    }

    private static function toBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $n = (int) $value;

        return match ($unit) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }
}
