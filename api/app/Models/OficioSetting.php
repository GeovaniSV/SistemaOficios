<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OficioSetting extends Model
{
    protected $table = 'oficio_settings';

    protected $fillable = [
        'statement_text',
    ];
}
