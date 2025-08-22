<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Adapter\FileSystem;

use App\Shared\Domain\Entity\Image as ImageEntity;

final class Image
{
    public function __construct(
        private ImageEntity $image
    ) {
    }

    public function store(string $targetFilename): bool
    {
        $extension = pathinfo($targetFilename, PATHINFO_EXTENSION);

        return match ($extension) {
            'jpg', 'jpeg' => imagejpeg($this->image->gd, $targetFilename),
            'png' => imagepng($this->image->gd, $targetFilename),
            'gif' => imagegif($this->image->gd, $targetFilename),
            'bmp' => imagebmp($this->image->gd, $targetFilename),
            'avif' => imageavif($this->image->gd, $targetFilename),
            'webp' => imagewebp($this->image->gd, $targetFilename),
        };
    }
}
