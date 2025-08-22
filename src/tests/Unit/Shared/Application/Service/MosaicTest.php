<?php

declare(strict_types=1);

namespace UnitTests\Shared\Application\Service;

use App\Shared\Application\Service\Mosaic;
use App\Shared\Domain\Entity\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\Shared\Application\Service\Mosaic::class)]
#[UsesClass(\App\Shared\Domain\Entity\Image::class)]
class MosaicTest extends TestCase
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
    #[Group('Shared')]
    public function testBuild(): void
    {
        $image = new Mosaic(2, 2)->build([
            0 => [
                'x1' => 0,
                'y1' => 0,
                'x2' => 0,
                'y2' => 0,
                'color' => ['r' => 255, 'g' => 255, 'b' => 255],
            ],
            1 => [
                'x1' => 1,
                'y1' => 0,
                'x2' => 1,
                'y2' => 0,
                'color' => ['r' => 0, 'g' => 255, 'b' => 0],
            ],
            2 => [
                'x1' => 0,
                'y1' => 1,
                'x2' => 0,
                'y2' => 1,
                'color' => ['r' => 255, 'g' => 0, 'b' => 0],
            ],
            3 => [
                'x1' => 1,
                'y1' => 1,
                'x2' => 1,
                'y2' => 1,
                'color' => ['r' => 0, 'g' => 0, 'b' => 255],
            ],
        ]);

        self::assertInstanceOf(Image::class, $image);
        self::assertEquals(2, $image->width);
        self::assertEquals(2, $image->height);
    }
}
