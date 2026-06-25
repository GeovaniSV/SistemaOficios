<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracoes.acessar');
    }

    public function rules(): array
    {
        return [

            'header' => [
                'required',
                'string'
            ],

            'footer' => [
                'required',
                'string'
            ],

            'signers' => [
                'present',
                'array',
            ],

            'signers.*.type' => [
                'required',
                'in:user,position',
            ],

            'signers.*.id' => [
                'required',
                'integer',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $seen = [];

            foreach ($this->input('signers', []) as $index => $entry) {
                $type = $entry['type'] ?? null;
                $id   = $entry['id'] ?? null;

                if (!in_array($type, ['user', 'position'], true) || !$id) {
                    continue;
                }

                $key = $type . ':' . $id;

                if (isset($seen[$key])) {
                    $validator->errors()->add(
                        "signers.{$index}.id",
                        'Este assinante já foi adicionado.'
                    );
                    continue;
                }

                $seen[$key] = true;

                $table  = $type === 'user' ? 'users' : 'positions';
                $exists = DB::table($table)->where('id', $id)->exists();

                if (!$exists) {
                    $validator->errors()->add(
                        "signers.{$index}.id",
                        "O {$type} informado não existe."
                    );
                }
            }
        });
    }
}
