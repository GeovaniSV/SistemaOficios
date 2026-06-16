<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OficioTemplate extends Model
{
    protected $table = 'oficio_templates';

    protected $fillable = [
        'name',
        'content',
    ];
}
