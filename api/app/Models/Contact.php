<?php

namespace App\Models;

use App\Enums\TipoContatoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $table = 'contacts';

    protected $fillable = [
        'type',
        'doc',
        'name',
        'address_id',
    ];

    protected $casts = [
        'type' => TipoContatoEnum::class,
    ];

    protected $appends = [
        'formatted_doc',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function responsibles(): HasMany
    {
        return $this->hasMany(Responsible::class, 'contact_id');
    }

    public function isPessoaFisica(): bool
    {
        return $this->type === TipoContatoEnum::PESSOA_FISICA;
    }

    public function isPessoaJuridica(): bool
    {
        return $this->type === TipoContatoEnum::PESSOA_JURIDICA;
    }

    public function getFormattedDocAttribute(): string
    {
        $doc = preg_replace('/\D/', '', $this->doc);

        if ($this->isPessoaFisica()) {

            return preg_replace(
                '/(\d{3})(\d{3})(\d{3})(\d{2})/',
                '$1.$2.$3-$4',
                $doc
            );
        }

        return preg_replace(
            '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
            '$1.$2.$3/$4-$5',
            $doc
        );
    }

    protected static function booted(): void
    {
        static::saving(function (Contact $contact) {

            $contact->doc = preg_replace(
                '/\D/',
                '',
                $contact->doc
            );

            if (
                $contact->isPessoaFisica()
                && strlen($contact->doc) !== 11
            ) {
                throw new \InvalidArgumentException(
                    'CPF inválido.'
                );
            }

            if (
                $contact->isPessoaJuridica()
                && strlen($contact->doc) !== 14
            ) {
                throw new \InvalidArgumentException(
                    'CNPJ inválido.'
                );
            }
        });
    }
}
