<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViewOficioTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('templates.ver');
    }

    public function rules(): array
    {
        return [];
    }
}
