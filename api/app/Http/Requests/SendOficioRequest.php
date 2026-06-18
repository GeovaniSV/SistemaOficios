<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sign-oficios');
    }

    public function rules(): array
    {
        return [];
    }
}
