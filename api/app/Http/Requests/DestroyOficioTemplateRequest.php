<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyOficioTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('templates.excluir');
    }

    public function rules(): array
    {
        return [
            'activate' => ['sometimes', 'boolean'],
        ];
    }
}
