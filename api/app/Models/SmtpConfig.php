<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpConfig extends Model
{
    protected $table = 'smtp_configs';

    protected $fillable = [
        'host',
        'port',
        'username',
        'password',
        'from_name',
        'from_email',
        'use_tls',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'port'     => 'integer',
            'use_tls'  => 'boolean',
            'password' => 'encrypted',
        ];
    }
}
