<?php

declare(strict_types=1);

namespace UnitTests\Shared\Infrastructure\Adapter\FileSystem;

use App\Shared\Domain\Entity\Image;
use App\Shared\Infrastructure\Adapter\FileSystem\Image as FileSystemImageAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\Shared\Infrastructure\Adapter\FileSystem\Image::class)]
#[UsesClass(\App\Shared\Domain\Entity\Image::class)]
class ImageTest extends TestCase
{
    private FileSystemImageAdapter $sut;

    public static function getFixturePath(string $filename): string
    {
        return dirname(__DIR__, 5) . '/Fixtures/' . $filename;
    }

    public static function getFixtureContents(string $filename): string
    {
        return file_get_contents(self::getFixturePath($filename));
    }

    protected function setUp(): void
    {
        $this->sut = new FileSystemImageAdapter(
            Image::fromFile(self::getFixturePath('2x2.png'))
        );
    }

    #[Test]
    #[Group('Shared')]
    #[DataProvider('dataProviderForStore')]
    public function testStore(string $filename): void
    {
        $status = $this->sut->store($filename);

        self::assertTrue($status);

        unlink($filename);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function dataProviderForStore(): array
    {
        return [
            '[JPG]' => ['/tmp/2x2.jpg'],
            '[JPEG]' => ['/tmp/2x2.jpeg'],
            '[PNG]' => ['/tmp/2x2.png'],
            '[GIF]' => ['/tmp/2x2.gif'],
            '[BMP]' => ['/tmp/2x2.bmp'],
            '[AVIF]' => ['/tmp/2x2.avif'],
            '[WEBP]' => ['/tmp/2x2.webp'],
        ];
    }
}
