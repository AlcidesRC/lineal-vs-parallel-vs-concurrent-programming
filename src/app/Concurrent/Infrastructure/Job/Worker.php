<?php

declare(strict_types=1);

namespace App\Concurrent\Infrastructure\Job;

use App\Concurrent\Infrastructure\Adapter\RabbitMQ;
use App\Shared\Application\Service\ImageArea;
use App\Shared\Domain\Entity\Image as ImageEntity;
use PhpAmqpLib\Message\AMQPMessage;

final class Worker
{
    /**
     * @throws \Exception
     */
    public function __invoke(?int $prefetchCount = 20, ?string $queueName = 'default'): void
    {
        $rabbit = new RabbitMQ($queueName);

        $rabbit->consume(prefetchCount: $prefetchCount, callback: function (AMQPMessage $message) {
            $message->ack();

            if ($message->getBody() === 'quit') {
                $message->getChannel()->basic_cancel($message->getConsumerTag());
                return;
            }

            $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);

            $x2 = $payload['x1'] + $payload['blockWidth'] - 1;
            $y2 = $payload['y1'] + $payload['blockHeight'] - 1;

            $area = imagecreatefromstring(base64_decode($payload['base64_encoded_area']));

            $data = [
                'x1' => $payload['x1'],
                'y1' => $payload['y1'],
                'x2' => $x2,
                'y2' => $y2,
                'color' => new ImageArea(new ImageEntity($area))->getAverageColor(
                    x1: 0,
                    y1: 0,
                    x2: $payload['blockWidth'] - 1,
                    y2: $payload['blockHeight'] - 1,
                ),
            ];

            imagedestroy($area);

            file_put_contents($payload['filename'], serialize($data) . PHP_EOL, FILE_APPEND);
        });

        $rabbit->disconnect();
    }
}
