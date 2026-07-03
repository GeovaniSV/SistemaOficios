<?php

namespace App\Enums;

enum BackupTypeEnum: string
{
    case AUTOMATIC = 'automatic';
    case MANUAL    = 'manual';
}
