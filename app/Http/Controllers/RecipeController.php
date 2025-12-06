<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecipeController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display the user's recipe dashboard.
     */
    public function index()
    {
        $recipes = Auth::user()->recipes()
            ->with('versions')
            ->latest()
            ->get();

        return view('recipes.index', compact('recipes'));
    }

    /**
     * Show the form for creating a new recipe.
     */
    public function create()
    {
        return view('recipes.create');
    }

    /**
     * Store a newly created recipe in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'servings' => 'required|integer|min:1',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.quantity' => 'required|numeric',
            'ingredients.*.unit' => 'required|string',
            'steps' => 'required|array',
            'steps.*.step_number' => 'required|integer',
            'steps.*.instruction' => 'required|string',
            'chat_message_id' => 'nullable|integer|exists:chat_messages,id',
        ]);

        $recipe = Auth::user()->recipes()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

        $recipe->versions()->create([
            'version_number' => 1,
            'servings' => $validated['servings'],
            'ingredients' => $validated['ingredients'],
            'steps' => $validated['steps'],
            'change_summary' => 'Initial version',
        ]);

        // If this recipe was created from a chat message, link it
        if ($request->filled('chat_message_id')) {
            $chatMessage = ChatMessage::find($validated['chat_message_id']);

            // Verify the message belongs to the authenticated user
            if ($chatMessage && $chatMessage->user_id === Auth::id()) {
                $chatMessage->update(['recipe_id' => $recipe->id]);
            }
        }

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe created successfully!');
    }

    /**
     * Display the specified recipe.
     */
    public function show(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $recipe->load(['versions.comments.user', 'versions.comments.resultVersion']);

        $latestVersion = $recipe->versions->sortByDesc('version_number')->first();

        // Get comments for the latest version, sorted chronologically
        $comments = $latestVersion ? $latestVersion->comments()->with(['user', 'resultVersion'])->orderBy('created_at', 'asc')->get() : collect();

        return view('recipes.show', compact('recipe', 'latestVersion', 'comments'));
    }

    /**
     * Show the form for editing the specified recipe.
     */
    public function edit(Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $recipe->load('versions');
        $latestVersion = $recipe->versions->sortByDesc('version_number')->first();

        return view('recipes.edit', compact('recipe', 'latestVersion'));
    }

    /**
     * Update the specified recipe in storage (creates new version).
     */
    public function update(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'servings' => 'required|integer|min:1',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.quantity' => 'required|numeric',
            'ingredients.*.unit' => 'required|string',
            'steps' => 'required|array',
            'steps.*.step_number' => 'required|integer',
            'steps.*.instruction' => 'required|string',
            'change_summary' => 'nullable|string',
        ]);

        $recipe->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

        $latestVersion = $recipe->versions->sortByDesc('version_number')->first();
        $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        $recipe->versions()->create([
            'version_number' => $newVersionNumber,
            'servings' => $validated['servings'],
            'ingredients' => $validated['ingredients'],
            'steps' => $validated['steps'],
            'change_summary' => $validated['change_summary'] ?? 'Manual edit',
        ]);

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe updated successfully!');
    }

    /**
     * Remove the specified recipe from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Recipe deleted successfully!');
    }

    /**
     * Show version history for a recipe.
     */
    public function history(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $recipe->load('versions');
        $versions = $recipe->versions->sortByDesc('version_number');
        $latestVersion = $versions->first();

        return view('recipes.history', compact('recipe', 'versions', 'latestVersion'));
    }

    /**
     * Show a specific version of a recipe.
     */
    public function showVersion(Recipe $recipe, RecipeVersion $version)
    {
        $this->authorize('view', $recipe);

        // Ensure the version belongs to this recipe
        if ($version->recipe_id !== $recipe->id) {
            abort(404);
        }

        // Load comments for this version
        $comments = $version->comments()->with(['user', 'resultVersion'])->orderBy('created_at', 'asc')->get();

        return view('recipes.show-version', compact('recipe', 'version', 'comments'));
    }
}
