<?php

declare(strict_types=1);

namespace Integration\Concurrent\Infrastructure\Adapter;

use App\Concurrent\Infrastructure\Adapter\RabbitMQ;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\Concurrent\Infrastructure\Adapter\RabbitMQ::class)]
final class RabbitMQTest extends TestCase
{
    #[Test]
    #[Group('Concurrent')]
    public function testReturnsFalseOnException(): void
    {
        $rabbit = new RabbitMQ('test-rabbitmq');
        $rabbit->disconnect();

        $statusPublish = $rabbit->publish('Lorem ipsum');

        $statusConsume = $rabbit->consume(
            prefetchCount: 1,
            callback: function (AMQPMessage $message) {
                $message->ack();
            }
        );

        self::assertFalse($statusPublish);
        self::assertFalse($statusConsume);
    }

    #[Test]
    #[Group('Concurrent')]
    public function testPublishPlainText(): void
    {
        $rabbit = new RabbitMQ('test-rabbitmq');

        $statusPublish = $rabbit->publish('Lorem ipsum');
        $rabbit->publish('quit');

        $statusConsume = $rabbit->consume(
            prefetchCount: 1,
            callback: function (AMQPMessage $message) {
                $message->ack();

                if ($message->getBody() === 'quit') {
                    $message->getChannel()->basic_cancel($message->getConsumerTag());
                    return;
                }

                self::assertIsString($message->getBody());
                self::assertEquals('Lorem ipsum', $message->getBody());
            }
        );

        self::assertTrue($statusPublish);
        self::assertTrue($statusConsume);
    }

    #[Test]
    #[Group('Concurrent')]
    public function testPublishArray(): void
    {
        $data = [
            'key' => 'value',
        ];

        $rabbit = new RabbitMQ('test-rabbitmq');

        $statusPublish = $rabbit->publish($data);
        $rabbit->publish('quit');

        $statusConsume = $rabbit->consume(
            prefetchCount: 1,
            callback: function (AMQPMessage $message) use ($data) {
                $message->ack();

                if ($message->getBody() === 'quit') {
                    $message->getChannel()->basic_cancel($message->getConsumerTag());
                    return;
                }

                $expectedJson = json_encode($data, JSON_THROW_ON_ERROR);

                self::assertJsonStringEqualsJsonString($expectedJson, $message->getBody());
            }
        );

        self::assertTrue($statusPublish);
        self::assertTrue($statusConsume);
    }
}
