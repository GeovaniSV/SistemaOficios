<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.excluir');
    }

    public function rules(): array
    {
        return [];
    }
}
