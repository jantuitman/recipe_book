<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(
        private OpenAiService $openAiService
    ) {}

    /**
     * Store a new comment on a recipe version.
     */
    public function store(Request $request, Recipe $recipe, RecipeVersion $version)
    {
        // Ensure the version belongs to the recipe
        if ($version->recipe_id !== $recipe->id) {
            abort(404);
        }

        // Ensure user owns the recipe
        if ($recipe->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        // Detect if comment contains feedback that could improve the recipe
        $hasFeedback = $this->openAiService->detectFeedback($validated['content']);

        $comment = $version->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'is_ai' => false,
            'has_feedback' => $hasFeedback,
        ]);

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Comment added successfully!');
    }
}
