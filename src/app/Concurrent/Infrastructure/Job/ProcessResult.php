<?php

declare(strict_types=1);

namespace App\Concurrent\Infrastructure\Job;

use App\Shared\Application\BaseProcessImage;
use App\Shared\Application\Service\Mosaic;
use App\Shared\Domain\Entity\Image as ImageEntity;
use App\Shared\Infrastructure\Adapter\FileSystem\Image as FileSystemImageAdapter;
use Generator;
use RuntimeException;

final class ProcessResult extends BaseProcessImage
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

        $map = self::generateMosaicMap($sourceFilename, $source, $blockWidth, $blockHeight);

        $target = new Mosaic($source->width, $source->height)->build($map);

        return new FileSystemImageAdapter($target)->store($targetFilename);
    }

    private static function generateMosaicMap(
        string $sourceFilename,
        ImageEntity $source,
        int $blockWidth,
        int $blockHeight,
    ): array {
        $expectedTotalLines = ($source->width / $blockWidth) * ($source->height / $blockHeight);

        $filename = strtr('{PATH}/{FILENAME}_{WIDTH}x{HEIGHT}', [
            '{PATH}' => rtrim(sys_get_temp_dir(), '/'),
            '{FILENAME}' => md5($sourceFilename),
            '{WIDTH}' => $blockWidth,
            '{HEIGHT}' => $blockHeight,
        ]);

        $currentTotalLines = exec(
            strtr('wc -l < {FILENAME}', [
                '{FILENAME}' => $filename
            ])
        );

        if ($currentTotalLines < $expectedTotalLines) {
            throw new RuntimeException('[ WIP ] Please check again later...');
        }

        $map = [];
        foreach (self::getLines($filename) as $line) {
            if (empty($line)) {
                continue;
            }
            $map[] = unserialize(str_replace(PHP_EOL, '', $line));
        }

        return $map;
    }

    private static function getLines(string $filename): Generator
    {
        $file = fopen($filename, 'r');

        while (!feof($file)) {
            yield fgets($file);
        }

        fclose($file);
    }
}
