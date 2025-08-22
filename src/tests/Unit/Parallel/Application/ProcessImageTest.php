<?php

declare(strict_types=1);

namespace UnitTests\Parallel\Application;

use App\Parallel\Application\ProcessImage;
use App\Shared\Application\BaseProcessImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(\App\Parallel\Application\ProcessImage::class)]
#[UsesClass(\App\Shared\Application\Service\Mosaic::class)]
#[UsesClass(\App\Shared\Domain\Entity\Image::class)]
#[UsesClass(\App\Shared\Infrastructure\Adapter\FileSystem\Image::class)]
final class ProcessImageTest extends TestCase
{
    public static function getFixturePath(string $filename): string
    {
        return dirname(__DIR__, 3) . '/Fixtures/' . $filename;
    }

    public static function getFixtureContents(string $filename): string
    {
        return file_get_contents(self::getFixturePath($filename));
    }

    #[Test]
    #[Group('Parallel')]
    public function testExceptionIsThrownWhenSourceFileDoesNotExist(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage(
            strtr(BaseProcessImage::EXCEPTION_FOR_SOURCE_FILE, [
                '{FILENAME}' => '/missing/path/source.xxx',
            ])
        );

        (new ProcessImage())->__invoke(
            '/missing/path/source.xxx',
            '/missing/path/target.xxx',
        );
    }

    #[Test]
    #[Group('Parallel')]
    public function testExceptionIsThrownWhenTargetFileIsNotWritable(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage(
            strtr(BaseProcessImage::EXCEPTION_FOR_TARGET_FILE, [
                '{FILENAME}' => '/missing/path/target.xxx',
            ])
        );

        (new ProcessImage())->__invoke(
            self::getFixturePath('2x2.png'),
            '/missing/path/target.xxx',
        );
    }

    #[Test]
    #[Group('Parallel')]
    #[DataProvider('dataProviderForInvoke')]
    public function testInvoke(
        string $sourceFilename,
        string $targetFilename,
        int $blockWidth,
        int $blockHeight,
    ): void {
        $status = (new ProcessImage())->__invoke($sourceFilename, $targetFilename, $blockWidth, $blockHeight);

        self::assertTrue($status);
        self::assertFileExists($targetFilename);
        self::assertFileIsReadable($targetFilename);
        self::assertGreaterThan(0, filesize($targetFilename));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function dataProviderForInvoke(): array
    {
        return [
            '5 x 5' => [
                self::getFixturePath('source.jpg'),
                self::getFixturePath('source_parallel_5x5.webp'),
                5,
                5,
            ],
            '10 x 10' => [
                self::getFixturePath('source.jpg'),
                self::getFixturePath('source_parallel_10x10.webp'),
                10,
                10,
            ],
            '20 x 20' => [
                self::getFixturePath('source.jpg'),
                self::getFixturePath('source_parallel_20x20.webp'),
                20,
                20,
            ],
        ];
    }
}
