<?php

namespace App\Http\Requests;

use App\Enums\InteractionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'author_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', Rule::in(array_map(fn (InteractionType $type) => $type->value, InteractionType::cases()))],
        ];
    }
}
