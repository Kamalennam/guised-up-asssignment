<?php

namespace App\Http\Controllers;

use App\Enums\InteractionType;
use App\Http\Requests\StoreInteractionRequest;
use App\Models\Interaction;
use Illuminate\Http\JsonResponse;

class InteractionController extends Controller
{
    public function store(StoreInteractionRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $interaction = Interaction::query()->firstOrCreate([
            'user_id' => $user->id,
            'post_id' => $validated['post_id'],
            'author_id' => $validated['author_id'],
            'type' => $validated['type'],
        ], [
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $interaction->id,
                'type' => $interaction->type->value,
            ],
        ], 201);
    }
}
