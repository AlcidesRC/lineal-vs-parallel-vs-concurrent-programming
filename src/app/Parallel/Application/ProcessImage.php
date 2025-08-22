<?php

declare(strict_types=1);

namespace App\Parallel\Application;

use App\Shared\Application\BaseProcessImage;
use App\Shared\Application\Service\Mosaic;
use App\Shared\Domain\Entity\Image as ImageEntity;
use App\Shared\Infrastructure\Adapter\FileSystem\Image as FileSystemImageAdapter;
use MTMan\Exceptions\MTManException;
use MTMan\MTMan;

final class ProcessImage extends BaseProcessImage
{
    private const array CONFIG = [
        'threads_count' => 8,
        'time_limit' => 30,
        'max_retries' => 3,
        'temp_dir' => '/tmp/',
    ];

    public function __invoke(
        string $sourceFilename,
        string $targetFilename,
        ?int $blockWidth = 20,
        ?int $blockHeight = 20,
    ): bool {
        self::checkSourceFilename($sourceFilename);
        self::checkTargetFilename($targetFilename);

        $source = ImageEntity::fromFile($sourceFilename);

        try {
            $map = self::generateMosaicMap($source, $blockWidth, $blockHeight);
        } catch (MTManException $exception) {
            $map = [];
        }

        $target = new Mosaic($source->width, $source->height)->build($map);

        return new FileSystemImageAdapter($target)->store($targetFilename);
    }

    /**
     * @throws \MTMan\Exceptions\MTManException
     */
    private static function generateMosaicMap(
        ImageEntity $image,
        int $blockWidth,
        int $blockHeight,
    ): array {
        $mtman = new MTMan(self::CONFIG);

        foreach (range(0, $image->height - 1, $blockHeight) as $y1) {
            $mtman->addTask(function () use ($image, $blockWidth, $blockHeight, $y1) {
                return array_map(function (int $x1) use ($image, $blockWidth, $blockHeight, $y1) {
                    return self::generateMosaicMapEntry($image, $blockWidth, $blockHeight, $x1, $y1);
                }, range(0, $image->width - 1, $blockWidth));
            });
        };

        return array_reduce($mtman->run(), 'array_merge', []);
    }
}
