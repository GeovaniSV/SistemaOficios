<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViewRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracoes.acessar');
    }

    public function rules(): array
    {
        return [];
    }
}
