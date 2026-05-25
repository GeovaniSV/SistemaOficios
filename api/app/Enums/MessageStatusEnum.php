<?php

namespace App\Enums;

enum MessageStatusEnum: string
{
    case PENDING = 'PENDING';
    case SENT = 'SENT';
    case ERROR = 'ERROR';
}
