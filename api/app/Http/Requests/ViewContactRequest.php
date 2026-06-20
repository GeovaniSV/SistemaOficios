<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViewContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('contatos.ver');
    }

    public function rules(): array
    {
        return [];
    }
}
