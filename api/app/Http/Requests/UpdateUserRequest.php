<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'email'       => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password'    => ['sometimes', 'string', 'min:8'],
            'cpf'         => ['sometimes', 'nullable', 'string', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$|^\d{11}$/'],
            'position_id' => ['sometimes', 'nullable', 'integer', 'exists:positions,id'],
            'role'        => ['sometimes', 'nullable', 'string', 'exists:roles,name'],
        ];
    }
}
