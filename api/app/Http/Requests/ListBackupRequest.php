<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->is_dev || $user->can('configuracoes.acessar');
    }

    public function rules(): array
    {
        return [];
    }
}
