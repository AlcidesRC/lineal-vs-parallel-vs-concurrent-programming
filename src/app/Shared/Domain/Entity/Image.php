<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entity;

use GdImage;

final readonly class Image
{
    public int $width;
    public int $height;

    public function __construct(
        public GdImage $gd,
        public ?string $extension = 'jpg',
    ) {
        $this->width = imagesx($this->gd);
        $this->height = imagesy($this->gd);
    }

    public static function fromFile(string $filename): self
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return new self(imagecreatefromstring(file_get_contents($filename)), $extension);
    }
}
