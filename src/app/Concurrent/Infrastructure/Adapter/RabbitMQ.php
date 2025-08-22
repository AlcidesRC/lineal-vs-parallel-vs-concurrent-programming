<?php

declare(strict_types=1);

namespace App\Concurrent\Infrastructure\Adapter;

use Closure;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class RabbitMQ
{
    private const string RABBITMQ_HOST = 'rabbitmq';
    private const int RABBITMQ_PORT = 5672;
    private const string RABBITMQ_USER = 'guest';
    private const string RABBITMQ_PASSWORD = 'guest';

    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    public function __construct(
        private ?string $queue = 'default'
    ) {
        $this->connection = new AMQPStreamConnection(
            host: self::RABBITMQ_HOST,
            port: self::RABBITMQ_PORT,
            user: self::RABBITMQ_USER,
            password: self::RABBITMQ_PASSWORD
        );

        $this->channel = $this->connection->channel();

        $this->channel->queue_declare(
            queue: $this->queue,
            auto_delete: false,
        );
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function disconnect()
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @throws \JsonException
     */
    public function publish(array|string $data): bool
    {
        $body = is_array($data)
            ? json_encode($data, JSON_THROW_ON_ERROR)
            : $data;

        try {
            $this->channel->basic_publish(
                msg: new AMQPMessage($body),
                routing_key: $this->queue,
            );
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    public function consume(int $prefetchCount, Closure $callback): bool
    {
        try {
            $this->channel->basic_qos(
                prefetch_size: 0,
                prefetch_count: $prefetchCount,
                a_global: false
            );

            $this->channel->basic_consume(
                queue: $this->queue,
                callback: $callback,
            );

            $this->channel->consume();
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
