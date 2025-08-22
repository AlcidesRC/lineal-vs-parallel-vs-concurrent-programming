<?php

declare(strict_types=1);

namespace Integration\Concurrent\Infrastructure\Job;

use App\Concurrent\Infrastructure\Job\ProcessResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(\App\Concurrent\Infrastructure\Job\ProcessResult::class)]
#[UsesClass(\App\Shared\Application\Service\Mosaic::class)]
#[UsesClass(\App\Shared\Domain\Entity\Image::class)]
#[UsesClass(\App\Shared\Infrastructure\Adapter\FileSystem\Image::class)]
final class ProcessResultTest extends TestCase
{
    public static function getFixturePath(string $filename): string
    {
        return dirname(__DIR__, 4) . '/Fixtures/' . $filename;
    }

    public static function getFixtureContents(string $filename): string
    {
        return file_get_contents(self::getFixturePath($filename));
    }

    #[Test]
    #[Group('Concurrent')]
    #[DataProvider('dataProviderForInvoke')]
    public function testThrowsAnExceptionWhenWIP(
        string $sourceFilename,
        string $targetFilename,
        int $blockWidth,
        int $blockHeight,
        string $resultFilename,
    ): void
    {
        file_put_contents('/tmp/' . $resultFilename, '');

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('[ WIP ] Please check again later...');

        (new ProcessResult())->__invoke($sourceFilename, $targetFilename, $blockWidth, $blockHeight);

        unlink('/tmp/' . $resultFilename);
    }

    #[Test]
    #[Group('Concurrent')]
    #[DataProvider('dataProviderForInvoke')]
    public function testInvoke(
        string $sourceFilename,
        string $targetFilename,
        int $blockWidth,
        int $blockHeight,
        string $resultFilename,
    ): void {
        copy(self::getFixturePath('Results/'. $resultFilename), '/tmp/' . $resultFilename);

        $status = (new ProcessResult())->__invoke($sourceFilename, $targetFilename, $blockWidth, $blockHeight);

        self::assertTrue($status);

        unlink('/tmp/' . $resultFilename);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function dataProviderForInvoke(): array
    {
        return [
            '5 x 5' => [
                self::getFixturePath('source.jpg'),
                self::getFixturePath('source_concurrent_5x5.webp'),
                5,
                5,
                'af3ad33051e9ea771fa42a4c9dbeed71_5x5',
            ],
            '10 x 10' => [
                self::getFixturePath('source.jpg'),
                self::getFixturePath('source_concurrent_10x10.webp'),
                10,
                10,
                'af3ad33051e9ea771fa42a4c9dbeed71_10x10',
            ],
            '20 x 20' => [
                self::getFixturePath('source.jpg'),
                self::getFixturePath('source_concurrent_20x20.webp'),
                20,
                20,
                'af3ad33051e9ea771fa42a4c9dbeed71_20x20',
            ],
        ];
    }
}
