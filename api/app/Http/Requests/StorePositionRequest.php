<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:191', 'unique:positions,name'],
            'description' => ['nullable', 'string', 'max:191'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
