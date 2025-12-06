<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
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

        $comment = $version->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'is_ai' => false,
        ]);

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Comment added successfully!');
    }
}
