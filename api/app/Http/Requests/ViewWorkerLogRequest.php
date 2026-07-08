<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewWorkerLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_dev;
    }

    public function rules(): array
    {
        return [
            'filter'                => ['sometimes', 'array'],
            'filter.worker'         => ['sometimes', 'string'],
            'filter.status'         => ['sometimes', 'string'],
            'filter.code'           => ['sometimes', 'string'],
            'filter.queue_name'     => ['sometimes', 'string'],
            'filter.correlation_id' => ['sometimes', 'string'],
            'filter.message'        => ['sometimes', 'string'],
            'sort'                  => ['sometimes', 'string', Rule::in(['created_at', '-created_at'])],
        ];
    }
}
