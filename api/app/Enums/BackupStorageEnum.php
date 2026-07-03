<?php

namespace App\Enums;

enum BackupStorageEnum: string
{
    case R2       = 'r2';
    case DOWNLOAD = 'download';
}
