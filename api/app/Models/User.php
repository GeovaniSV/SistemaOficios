<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'cpf', 'position_id', 'is_active', 'last_login'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login'        => 'datetime',
            'is_active'         => 'boolean',
            'is_dev'            => 'boolean',
            'password'          => 'hashed',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->formatCpf($value),
            set: function (?string $value) {
                if (empty($value)) {
                    return null;
                }

                $cpf = preg_replace('/\D/', '', $value);

                if (\strlen($cpf) !== 11) {
                    throw new InvalidArgumentException('CPF deve possuir exatamente 11 dígitos.');
                }

                return $cpf;
            }
        );
    }

    private function formatCpf(?string $cpf): ?string
    {
        if (!$cpf || \strlen($cpf) !== 11) {
            return $cpf;
        }

        return preg_replace(
            '/(\d{3})(\d{3})(\d{3})(\d{2})/',
            '$1.$2.$3-$4',
            $cpf
        );
    }

    public function oficios(): HasMany
    {
        return $this->hasMany(Oficio::class, 'author_id');
    }

    public function rejectionInfos(): HasMany
    {
        return $this->hasMany(RejectionInfo::class, 'author_id');
    }
}
