<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:191', Rule::unique('positions', 'name')->ignore($this->route('position'))],
            'description' => ['sometimes', 'nullable', 'string', 'max:191'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
