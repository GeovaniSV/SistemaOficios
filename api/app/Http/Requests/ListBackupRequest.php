<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->is_dev || $user->can('configuracoes.acessar');
    }

    public function rules(): array
    {
        return [
            'filter'                => ['sometimes', 'array'],
            'filter.type'           => ['sometimes', 'in:automatic,manual'],
            'filter.storage_type'   => ['sometimes', 'in:r2,download'],
            'filter.filename'       => ['sometimes', 'string'],
            'filter.is_available'   => ['sometimes', 'boolean'],
            'sort'                  => ['sometimes', 'string', Rule::in(['created_at', '-created_at'])],
        ];
    }
}
