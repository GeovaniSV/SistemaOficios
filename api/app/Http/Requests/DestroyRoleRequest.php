<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracoes.acessar');
    }

    public function rules(): array
    {
        return [
            'activate' => ['sometimes', 'boolean'],
        ];
    }
}
