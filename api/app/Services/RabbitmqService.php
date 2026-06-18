<?php

namespace App\Services;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;


class RabbitmqService{


    protected ?AMQPStreamConnection $connection = null;
    protected ?AMQPChannel $channel = null;

    protected function connect(): void
    {
        if ($this->connection !== null) {
            return;
        }

        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password'),
            config('rabbitmq.vhost')
        );
    }

    protected function openChannel(): void
    {
        $this->connect();

        if($this->channel !== null){
            return;
        }

        $this->channel = $this->connection->channel();
    }

    protected function declareQueue(string $queue): void
    {
        $this->channel->queue_declare(
            queue: $queue,
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false
        );
    }

    public function publish(
        \JsonSerializable $payload,
        ?string $queue = null,
        ?string $exchange = null,
        ?string $routingKey = null
    ): void
    {
        $queue      = $queue ?? config('rabbitmq.queue');
        $exchange   = $exchange ?? config('rabbitmq.exchange');
        $routingKey = $routingKey ?? config('rabbitmq.routing_key');

        $this->openChannel();
        $this->declareQueue($queue);

        $message = new AMQPMessage(
            body: json_encode($payload),
            properties: [
                'content_type' => 'application/json',
                'delivery_mode' => 2,
            ]
        );

        $this->channel->basic_publish(
            msg: $message,
            exchange: $exchange,
            routing_key: $routingKey
        );
    }

    public function close(): void
    {
        try {

            if ($this->channel !== null) {
                $this->channel->close();
                $this->channel = null;
            }

            if ($this->connection !== null) {
                $this->connection->close();
                $this->connection = null;
            }

        } catch (Exception $e) {

            report($e);

        }
    }

    public function __destruct()
    {
        $this->close();
    }
}




// É o Coelho MQ
