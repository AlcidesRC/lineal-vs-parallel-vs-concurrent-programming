<?php

declare(strict_types=1);

namespace Integration\Concurrent\Infrastructure\Job;

use App\Concurrent\Infrastructure\Adapter\RabbitMQ;
use App\Concurrent\Infrastructure\Job\Worker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\Concurrent\Infrastructure\Job\Worker::class)]
#[UsesClass(\App\Concurrent\Infrastructure\Adapter\RabbitMQ::class)]
#[UsesClass(\App\Shared\Application\Service\ImageArea::class)]
#[UsesClass(\App\Shared\Domain\Entity\Image::class)]
final class WorkerTest extends TestCase
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
    public function testInvoke(): void
    {
        $filename = '/tmp/worker';

        if (file_exists($filename)) {
            unlink($filename);
        }

        // Extract area
        $source = imagecreatefromstring(self::getFixtureContents('2x2.png'));
        $area = imagecreatetruecolor(1, 1);
        imagecopyresampled($area, $source, 0, 0, 0, 0, 1, 1, 1, 1);

        // Convert GD into Base64
        ob_start ();
        imagejpeg($area);
        $contents = ob_get_contents();
        ob_end_clean ();

        // Destroy images
        imagedestroy($area);
        imagedestroy($source);

        // Publish

        $rabbit = new RabbitMQ('test-worker');
        $statusPublish = $rabbit->publish([
            'filename' => $filename,
            'base64_encoded_area' => base64_encode($contents),
            'blockWidth' => 1,
            'blockHeight' => 1,
            'x1' => 0,
            'y1' => 0,
        ]);
        $rabbit->publish('quit');
        $rabbit->disconnect();

        self::assertTrue($statusPublish);

        // Consume

        (new Worker)->__invoke(2, 'test-worker');

        // Check

        self::assertFileExists($filename);
        self::assertFileIsReadable($filename);
        self::assertGreaterThan(0, filesize($filename));

        $expectedSerialized = [
            0 => serialize([
                'x1' => 0,
                'y1' => 0,
                'x2' => 0,
                'y2' => 0,
                'color' => [
                    'r' => 255,
                    'g' => 255,
                    'b' => 255,
                ],
            ]),
        ];

        $lines = array_map(function (string $line): string {
            return trim($line, PHP_EOL);
        }, file($filename));

        self::assertEquals($expectedSerialized, $lines);
    }
}
