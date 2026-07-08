<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewOficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('oficios.ver');
    }

    public function rules(): array
    {
        return [
            'filter'                          => ['sometimes', 'array'],
            'filter.subject'                  => ['sometimes', 'string'],
            'filter.number'                   => ['sometimes', 'string'],
            'filter.department'               => ['sometimes', 'string'],
            'filter.status'                   => ['sometimes', 'in:DRAFT,PENDING,APPROVED,SENT,REJECTED,RETURNED'],
            'filter.priority'                 => ['sometimes', 'in:LOW,MEDIUM,HIGH,URGENT'],
            'filter.destination_contact_id'   => ['sometimes', 'integer', 'exists:contacts,id'],
            'filter.author_id'                => ['sometimes', 'integer', 'exists:users,id'],
            'sort'                            => ['sometimes', 'string', Rule::in([
                'number', '-number', 'subject', '-subject', 'priority', '-priority',
                'status', '-status', 'created_at', '-created_at',
            ])],
        ];
    }
}
