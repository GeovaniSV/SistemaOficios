<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOficioTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('templates.criar');
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:191'
            ],

            'content' => [
                'required',
                'string'
            ],
        ];
    }
}
