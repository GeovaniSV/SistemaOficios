<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViewUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.ver');
    }

    public function rules(): array
    {
        return [];
    }
}
