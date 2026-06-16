<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
                'required',
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

            'department' => [
                'nullable',
                'string',
                'max:191'
            ],

            'responsibles' => [
                'required',
                'array',
                'min:1'
            ],

            'responsibles.*' => [
                'exists:responsibles,id'
            ],
        ];
    }
}
