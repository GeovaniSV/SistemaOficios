<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.ver');
    }

    public function rules(): array
    {
        return [
            'filter'              => ['sometimes', 'array'],
            'filter.name'         => ['sometimes', 'string'],
            'filter.email'        => ['sometimes', 'string'],
            'filter.position_id'  => ['sometimes', 'integer', 'exists:positions,id'],
            'filter.is_active'    => ['sometimes', 'boolean'],
            'filter.roles'        => ['sometimes', 'string', 'exists:roles,name'],
            'sort'                => ['sometimes', 'string', Rule::in([
                'name', '-name', 'email', '-email', 'created_at', '-created_at', 'last_login', '-last_login',
            ])],
        ];
    }
}
