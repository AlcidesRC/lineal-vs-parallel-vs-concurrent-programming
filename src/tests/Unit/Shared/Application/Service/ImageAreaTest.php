<?php

declare(strict_types=1);

namespace UnitTests\Shared\Application\Service;

use App\Shared\Application\Service\ImageArea;
use App\Shared\Domain\Entity\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\Shared\Application\Service\ImageArea::class)]
#[UsesClass(\App\Shared\Domain\Entity\Image::class)]
class ImageAreaTest extends TestCase
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
    #[DataProvider('dataProviderForGetAverageColor')]
    public function testGetAverageColor(int $x1, int $y1, int $x2, int $y2, array $expectedColor): void
    {
        $imageArea = new ImageArea(Image::fromFile(self::getFixturePath('2x2.png')));
        $color = $imageArea->getAverageColor($x1, $y1, $x2, $y2);

        self::assertIsArray($color);
        self::assertEquals($expectedColor, $color);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function dataProviderForGetAverageColor(): array
    {
        return [
            '[WHITE]' => [0, 0, 0, 0, ['r' => 255, 'g' => 255, 'b' => 255]],
            '[GREEN]' => [1, 0, 1, 0, ['r' => 0, 'g' => 255, 'b' => 0]],
            '[RED]' => [0, 1, 0, 1, ['r' => 255, 'g' => 0, 'b' => 0]],
            '[BLUE]' => [1, 1, 1, 1, ['r' => 0, 'g' => 0, 'b' => 255]],
        ];
    }
}
