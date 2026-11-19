<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required_without:visibility', 'sometimes', 'string', 'min:1', 'max:500'],
            'visibility' => ['required_without:body', 'sometimes', Rule::in(['public', 'members'])],
        ];
    }
}
