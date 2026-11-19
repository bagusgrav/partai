<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:1', 'max:500'],
            'visibility' => ['sometimes', 'required', Rule::in(['public', 'members'])],
        ];
    }
}
