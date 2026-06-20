<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('oficios.editar');
    }

    public function rules(): array
    {
        return [

            'subject' => [
                'required',
                'string',
                'max:191'
            ],

            'destination_contact_id' => [
                'nullable',
                'exists:contacts,id'
            ],

            'priority' => [
                'required',
                'in:LOW,MEDIUM,HIGH'
            ],

            'content' => [
                'required',
                'string'
            ],

            'responsibles' => [
                'required',
                'array',
                'min:1'
            ],

            'responsibles.*' => [
                'exists:responsibles,id'
            ],


            'department' => [
                'nullable',
                'string',
                'max:191'
            ],

            'submit' => [
                'sometimes',
                'boolean'
            ],
        ];
    }
}
