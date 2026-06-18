<?php

namespace App\Http\Requests;

use App\Enums\OficioStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewOficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sign-oficios');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                OficioStatusEnum::APPROVED->value,
                OficioStatusEnum::REJECTED->value,
                OficioStatusEnum::RETURNED->value,
            ])],

            'reason' => [
                Rule::requiredIf(fn() => in_array($this->input('status'), [
                    OficioStatusEnum::REJECTED->value,
                    OficioStatusEnum::RETURNED->value,
                ], true)),
                'nullable',
                'string',
            ],

            'subject'                => [
                'sometimes',
                'string',
                'max:191'
            ],

            'destination_contact_id' => [
                'sometimes',
                'exists:contacts,id'
            ],

            'priority'               => [
                'sometimes',
                'in:LOW,MEDIUM,HIGH'
            ],

            'content'                => [
                'sometimes',
                'string'
            ],

            'department'             => [
                'sometimes',
                'string',
                'max:191'
            ],

            'responsibles'           => [
                'sometimes',
                'array',
                'min:1'
            ],

            'responsibles.*'         => [
                'exists:responsibles,id'
            ],
        ];
    }
}
