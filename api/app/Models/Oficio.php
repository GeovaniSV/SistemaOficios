<?php

namespace App\Models;

use App\Enums\OficioPriorityEnum;
use App\Enums\OficioStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oficio extends Model
{
    protected $table = 'oficios';

    protected $fillable = [
        'subject',
        'destination_contact_id',
        'author_id',
        'priority',
        'content',
        'status',
        'department',
        'rejection_info_id',
    ];

    protected $casts = [
        'priority' => OficioPriorityEnum::class,
        'status' => OficioStatusEnum::class,
    ];

    public function destinationContact(): BelongsTo
    {
        return $this->belongsTo(
            Contact::class,
            'destination_contact_id'
        );
    }

    public function responsibles(): BelongsToMany
    {
        return $this->belongsToMany(
            Responsible::class,
            'oficio_responsible'
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rejectionInfo(): BelongsTo
    {
        return $this->belongsTo(RejectionInfo::class);
    }
}
