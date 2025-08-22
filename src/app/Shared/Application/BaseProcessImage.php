<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Application\Service\ImageArea;
use App\Shared\Domain\Entity\Image;
use RuntimeException;

class BaseProcessImage
{
    public const string EXCEPTION_FOR_SOURCE_FILE = 'File [ {FILENAME} ] does not exist or is not readable';
    public const string EXCEPTION_FOR_TARGET_FILE = 'File [ {FILENAME} ] is not writeable';

    protected static function checkSourceFilename(string $sourceFilename): void
    {
        if (!file_exists($sourceFilename) || !is_readable($sourceFilename)) {
            throw new RuntimeException(
                strtr(self::EXCEPTION_FOR_SOURCE_FILE, [
                    '{FILENAME}' => $sourceFilename,
                ])
            );
        }
    }

    protected static function checkTargetFilename(string $targetFilename): void
    {
        $targetPath = pathinfo($targetFilename, PATHINFO_DIRNAME);

        if (!is_writable($targetPath)) {
            throw new RuntimeException(
                strtr(self::EXCEPTION_FOR_TARGET_FILE, [
                    '{FILENAME}' => $targetFilename
                ])
            );
        }
    }

    protected static function generateMosaicMapEntry(
        Image $image,
        int $blockWidth,
        int $blockHeight,
        int $x1,
        int $y1,
    ): array {
        $x2 = $x1 + $blockWidth - 1;
        $y2 = $y1 + $blockHeight - 1;

        return [
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x2,
            'y2' => $y2,
            'color' => new ImageArea($image)->getAverageColor($x1, $y1, $x2, $y2),
        ];
    }
}
