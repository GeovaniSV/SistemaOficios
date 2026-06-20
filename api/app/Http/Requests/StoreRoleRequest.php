<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracoes.acessar');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('roles', 'name'),
            ],

            'description' => [
                'required',
                'string',
                'max:191',
            ],

            'status' => [
                'required',
                Rule::in(['Ativo', 'Inativo']),
            ],

            'permissions' => [
                'present',
                'array',
            ],

            'permissions.*' => [
                'string',
                'exists:permissions,name',
            ],
        ];
    }
}
