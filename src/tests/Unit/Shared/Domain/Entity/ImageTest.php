<?php

declare(strict_types=1);

namespace UnitTests\Shared\Domain\Entity;

use App\Shared\Domain\Entity\Image as ImageEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\Shared\Domain\Entity\Image::class)]
class ImageTest extends TestCase
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
    public function testInstance(): void
    {
        $instance = new ImageEntity(
            imagecreatefromstring(self::getFixtureContents('2x2.png')),
            'png'
        );

        self::assertInstanceOf(ImageEntity::class, $instance);
        self::assertEquals('png', $instance->extension);
        self::assertEquals(2, $instance->width);
        self::assertEquals(2, $instance->height);
    }

    #[Test]
    #[Group('Shared')]
    public function testFromFile(): void
    {
        $instance = ImageEntity::fromFile(self::getFixturePath('2x2.png'));

        self::assertInstanceOf(ImageEntity::class, $instance);
        self::assertEquals('png', $instance->extension);
        self::assertEquals(2, $instance->width);
        self::assertEquals(2, $instance->height);
    }
}
