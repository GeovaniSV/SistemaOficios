<?php


return [
    'host' => env('RABBITMQ_HOST'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER'),
    'password' => env('RABBITMQ_PASSWORD'),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'queue' => env('RABBITMQ_QUEUE'),
    'exchange' => env('RABBITMQ_EXCHANGE'),
    'routing_key' => env('RABBITMQ_ROUTING_KEY'),
    'email_queue' => env('RABBITMQ_EMAIL_QUEUE', 'email_queue'),
];
