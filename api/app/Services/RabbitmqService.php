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

        dd([
            'host' => config('rabbitmq.host'),
            'port' => config('rabbitmq.port'),
            'user' => config('rabbitmq.user'),
            'password' => config('rabbitmq.password'),
            'vhost' => config('rabbitmq.vhost'),
        ]);

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

        $this->declareQueue();
    }

    protected function declareQueue(): void
    {
        $this->channel->queue_declare(
            queue: config('rabbitmq.queue'),
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false
        );
    }

    public function publish(\JsonSerializable $payload): void
    {
        $this->openChannel();

        $message = new AMQPMessage(
            body: json_encode($payload),
            properties: [
                'content_type' => 'application/json',
                'delivery_mode' => 2,
            ]
        );

        $this->channel->basic_publish(
            msg: $message,
            exchange: config('rabbitmq.exchange'),
            routing_key: config('rabbitmq.routing_key')
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
