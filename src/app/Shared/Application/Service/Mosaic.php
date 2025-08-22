<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\Entity\Image as ImageEntity;

final readonly class Mosaic
{
    public function __construct(
        private int $width,
        private int $height,
    ) {
    }

    public function build(array $map): ImageEntity
    {
        $gd = imagecreatetruecolor($this->width, $this->height);

        array_walk($map, function (array &$entry) use ($gd) {
            imagefilledrectangle(
                $gd,
                $entry['x1'],
                $entry['y1'],
                $entry['x2'],
                $entry['y2'],
                imagecolorallocate(
                    $gd,
                    $entry['color']['r'],
                    $entry['color']['g'],
                    $entry['color']['b']
                )
            );
        });

        return new ImageEntity($gd);
    }
}
