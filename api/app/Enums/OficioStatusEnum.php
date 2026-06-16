<?php

namespace App\Enums;

enum OficioStatusEnum: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case SENT = 'SENT';
    case REJECTED = 'REJECTED';
    case RETURNED = 'RETURNED';
}
