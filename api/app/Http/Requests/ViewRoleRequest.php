<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracoes.acessar');
    }

    public function rules(): array
    {
        return [
            'filter'         => ['sometimes', 'array'],
            'filter.name'    => ['sometimes', 'string'],
            'filter.status'  => ['sometimes', Rule::in(['Ativo', 'Inativo'])],
            'sort'           => ['sometimes', 'string', Rule::in(['name', '-name', 'created_at', '-created_at'])],
        ];
    }
}
