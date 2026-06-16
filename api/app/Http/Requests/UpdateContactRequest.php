<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'type' => [
                'required',
                'in:PF,PJ'
            ],

            'name' => [
                'required',
                'string',
                'max:191'
            ],


            'address' => [
                'required',
                'array'
            ],

            'address.cep' => [
                'required',
                'string',
                'size:8'
            ],

            'address.logradouro' => [
                'required',
                'string'
            ],

            'address.numero' => [
                'required',
                'string'
            ],

            'address.bairro' => [
                'required',
                'string'
            ],

            'address.cidade' => [
                'required',
                'string'
            ],

            'address.estado' => [
                'required',
                'string',
                'size:2'
            ],


            'responsibles' => [
                'required',
                'array',
                'min:1'
            ],
        ];
    }
}
