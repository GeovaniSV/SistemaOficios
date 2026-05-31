<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkerLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correlationId' => ['nullable', 'string'],
            'code'          => ['nullable', 'string'],
            'message'       => ['nullable', 'string'],
            'status'        => ['nullable', 'integer'],
            'queueName'     => ['nullable', 'string'],
            'eventType'     => ['nullable', 'string'],
            'metadata'      => ['nullable', 'array'],
            'userId'        => ['nullable', 'string'],
        ];
    }
}
