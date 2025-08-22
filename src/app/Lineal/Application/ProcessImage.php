<?php

declare(strict_types=1);

namespace App\Lineal\Application;

use App\Shared\Application\BaseProcessImage;
use App\Shared\Application\Service\Mosaic;
use App\Shared\Domain\Entity\Image as ImageEntity;
use App\Shared\Infrastructure\Adapter\FileSystem\Image as FileSystemImageAdapter;

final class ProcessImage extends BaseProcessImage
{
    public function __invoke(
        string $sourceFilename,
        string $targetFilename,
        ?int $blockWidth = 20,
        ?int $blockHeight = 20,
    ): bool {
        self::checkSourceFilename($sourceFilename);
        self::checkTargetFilename($targetFilename);

        $source = ImageEntity::fromFile($sourceFilename);

        $map = self::generateMosaicMap($source, $blockWidth, $blockHeight);

        $target = new Mosaic($source->width, $source->height)->build($map);

        return new FileSystemImageAdapter($target)->store($targetFilename);
    }

    private static function generateMosaicMap(
        ImageEntity $image,
        int $blockWidth,
        int $blockHeight,
    ): array {
        $map = array_map(function (int $y1) use ($image, $blockWidth, $blockHeight) {
            return array_map(function (int $x1) use ($image, $blockWidth, $blockHeight, $y1) {
                return self::generateMosaicMapEntry($image, $blockWidth, $blockHeight, $x1, $y1);
            }, range(0, $image->width - 1, $blockWidth));
        }, range(0, $image->height - 1, $blockHeight));

        return array_reduce($map, 'array_merge', []);
    }
}
