<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('contatos.excluir');
    }

    public function rules(): array
    {
        return [
            'activate' => ['sometimes', 'boolean'],
        ];
    }
}
