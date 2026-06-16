<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Responsible extends Model
{
    protected $table = 'responsibles';

    protected $fillable = [
        'contact_id',
        'name',
        'email',
        'treatment',
        'position',
        'department',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function oficios(): BelongsToMany
    {
        return $this->belongsToMany(
            Oficio::class,
            'oficio_responsible'
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
