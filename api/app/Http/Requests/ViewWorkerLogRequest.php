<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViewWorkerLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_dev;
    }

    public function rules(): array
    {
        return [];
    }
}
