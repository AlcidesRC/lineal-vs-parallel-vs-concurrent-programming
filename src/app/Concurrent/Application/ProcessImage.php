<?php

declare(strict_types = 1);

namespace App\Concurrent\Application;

use App\Concurrent\Infrastructure\Adapter\RabbitMQ;
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
        $filename = strtr('{PATH}/{FILENAME}_{WIDTH}x{HEIGHT}', [
            '{PATH}' => rtrim(sys_get_temp_dir(), '/'),
            '{FILENAME}' => md5($sourceFilename),
            '{WIDTH}' => $blockWidth,
            '{HEIGHT}' => $blockHeight,
        ]);

        self::putJobsIntoQueue($filename, $sourceFilename, $source, $blockWidth, $blockHeight);

        // Map is built in background
        return [];
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    private static function putJobsIntoQueue(
        string $filename,
        string $sourceFilename,
        ImageEntity $source,
        int $blockWidth,
        int $blockHeight,
    ): void {
        $rabbit = new RabbitMQ('default');

        array_map(function(int $y1) use ($filename, $sourceFilename, $source, $blockWidth, $blockHeight, $rabbit) {
            array_map(function(int $x1) use ($filename, $sourceFilename, $source, $blockWidth, $blockHeight, $y1, $rabbit) {
                // Extract area
                $area = imagecreatetruecolor($blockWidth, $blockHeight);
                imagecopyresampled($area, $source->gd, 0, 0, $x1, $y1, $blockWidth, $blockHeight, $blockWidth, $blockHeight);

                // Convert GD into Base64
                ob_start ();
                imagejpeg($area);
                $contents = ob_get_contents();
                ob_end_clean ();

                // Destroy area
                imagedestroy($area);

                // Send message
                $data = [
                    'filename' => $filename,
                    'blockWidth' => $blockWidth,
                    'blockHeight' => $blockHeight,
                    'x1' => $x1,
                    'y1' => $y1,
                    'base64_encoded_area' => base64_encode($contents),
                ];

                $rabbit->publish($data);
            }, range(0, $source->width - 1, $blockWidth));
        }, range(0, $source->height - 1, $blockHeight));
    }
}
