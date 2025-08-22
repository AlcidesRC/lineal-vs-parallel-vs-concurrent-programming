<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\Entity\Image as ImageEntity;

final readonly class ImageArea
{
    public function __construct(
        private ImageEntity $image
    ) {
    }

    public function getAverageColor(
        int $x1,
        int $y1,
        int $x2,
        int $y2,
    ): array {
        $r = $g = $b = [];

        foreach (range($x1, $x2) as $x) {
            foreach (range($y1, $y2) as $y) {
                $colors = imagecolorsforindex($this->image->gd, imagecolorat($this->image->gd, $x, $y));

                $r[] = $colors['red'];
                $g[] = $colors['green'];
                $b[] = $colors['blue'];
            }
        }

        return [
            'r' => (int) (array_sum($r) / count($r)),
            'g' => (int) (array_sum($g) / count($g)),
            'b' => (int) (array_sum($b) / count($b)),
        ];
    }
}
