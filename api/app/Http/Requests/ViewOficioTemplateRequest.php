<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewOficioTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('templates.ver');
    }

    public function rules(): array
    {
        return [
            'filter'           => ['sometimes', 'array'],
            'filter.name'      => ['sometimes', 'string'],
            'filter.is_active' => ['sometimes', 'boolean'],
            'sort'             => ['sometimes', 'string', Rule::in(['name', '-name', 'created_at', '-created_at'])],
        ];
    }
}
