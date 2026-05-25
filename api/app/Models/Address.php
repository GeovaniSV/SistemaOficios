<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'cep',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'estado',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'address_id');
    }

    public function getFullAddressAttribute(): string
    {
        return sprintf(
            '%s, %s - %s - %s/%s - CEP: %s',
            $this->logradouro,
            $this->numero,
            $this->bairro,
            $this->cidade,
            $this->estado,
            $this->cep
        );
    }
}
