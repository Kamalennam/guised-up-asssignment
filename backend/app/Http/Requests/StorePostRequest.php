<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|callable>>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:1', 'max:2000'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ];
    }
}
