<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\ChatMessage;
use App\Models\Comment;
use App\Services\OpenAiService;
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

        $recipe->load(['versions.comments.user', 'versions.comments.resultVersion', 'versions.comments.recipeVersion']);

        $latestVersion = $recipe->versions->sortByDesc('version_number')->first();

        // Get ALL comments from ALL versions, sorted chronologically
        $comments = $recipe->versions
            ->flatMap(fn($version) => $version->comments)
            ->sortBy('created_at')
            ->values();

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

    /**
     * Show feedback chat interface for recipe improvement.
     */
    public function feedback(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $latestVersion = $recipe->versions()->orderBy('version_number', 'desc')->first();

        return view('recipes.feedback', compact('recipe', 'latestVersion'));
    }

    /**
     * Process feedback and get AI suggestions for recipe improvement.
     */
    public function processFeedback(Request $request, Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $validated = $request->validate([
            'feedback' => 'required|string|min:3|max:1000',
            'recipe' => 'required|array',
            'recipe.ingredients' => 'required|array',
            'recipe.steps' => 'required|array',
        ]);

        try {
            $openAiService = app(\App\Services\OpenAiService::class);

            // Get AI suggestions for improvement
            $suggestions = $openAiService->improveRecipe(
                $validated['recipe'],
                $validated['feedback']
            );

            // Store suggestions in session for later application
            session()->put('recipe_suggestions_' . $recipe->id, [
                'suggestions' => $suggestions,
                'feedback' => $validated['feedback'],
                'timestamp' => now()->toIso8601String()
            ]);

            return response()->json([
                'success' => true,
                'message' => "I've analyzed your feedback and prepared some improvements. Here's what I suggest:\n\n" .
                            $suggestions['change_summary'],
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            \Log::error('Recipe feedback processing failed', [
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sorry, I encountered an error processing your feedback. Please try again.'
            ], 500);
        }
    }

    /**
     * Apply AI suggestions and create a new recipe version.
     */
    public function applySuggestions(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        // Retrieve suggestions from session
        $sessionKey = 'recipe_suggestions_' . $recipe->id;
        $sessionData = session()->get($sessionKey);

        if (!$sessionData || !isset($sessionData['suggestions'])) {
            return response()->json([
                'success' => false,
                'message' => 'No suggestions found. Please provide feedback first.'
            ], 400);
        }

        $suggestions = $sessionData['suggestions'];
        $feedback = $sessionData['feedback'];

        // Get the current latest version number
        $latestVersion = $recipe->versions()->orderBy('version_number', 'desc')->first();
        $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        // Create new version with AI suggestions
        $newVersion = $recipe->versions()->create([
            'version_number' => $newVersionNumber,
            'servings' => $suggestions['servings'] ?? $latestVersion->servings ?? 4,
            'ingredients' => $suggestions['ingredients'],
            'steps' => $suggestions['steps'],
            'change_summary' => $suggestions['change_summary'] ?? 'AI-suggested improvements based on user feedback'
        ]);

        // Create AI comment linking to this version
        $newVersion->comments()->create([
            'user_id' => null, // AI comment
            'content' => "Based on your feedback: \"{$feedback}\"\n\n" . $suggestions['change_summary'],
            'is_ai' => true,
            'result_version_id' => $newVersion->id
        ]);

        // Clear the session data
        session()->forget($sessionKey);

        return response()->json([
            'success' => true,
            'message' => 'Recipe updated successfully with AI suggestions!',
            'version_number' => $newVersionNumber,
            'redirect_url' => route('recipes.show', $recipe)
        ]);
    }

    /**
     * Generate improvement suggestions based on a comment's feedback.
     */
    public function improveFromComment(Request $request, Recipe $recipe, Comment $comment)
    {
        $this->authorize('update', $recipe);

        // Verify the comment belongs to this recipe
        $commentVersion = $comment->recipeVersion;
        if (!$commentVersion || $commentVersion->recipe_id !== $recipe->id) {
            return response()->json([
                'success' => false,
                'message' => 'Comment does not belong to this recipe.'
            ], 400);
        }

        // Verify it's a feedback comment
        if (!$comment->has_feedback) {
            return response()->json([
                'success' => false,
                'message' => 'This comment does not contain feedback.'
            ], 400);
        }

        try {
            $openAiService = app(OpenAiService::class);

            // Get the latest version of the recipe
            $latestVersion = $recipe->versions()->orderBy('version_number', 'desc')->first();

            // Build recipe data for improvement
            $recipeData = [
                'name' => $recipe->name,
                'servings' => $latestVersion->servings,
                'ingredients' => $latestVersion->ingredients,
                'steps' => $latestVersion->steps,
            ];

            // Get AI suggestions using comment content as feedback
            $suggestions = $openAiService->improveRecipe($recipeData, $comment->content);

            // Store suggestions in session with comment_id for later application
            session()->put('recipe_suggestions_' . $recipe->id, [
                'suggestions' => $suggestions,
                'feedback' => $comment->content,
                'comment_id' => $comment->id,
                'timestamp' => now()->toIso8601String()
            ]);

            return response()->json([
                'success' => true,
                'message' => "I've analyzed your feedback and prepared some improvements.",
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            \Log::error('Recipe improvement from comment failed', [
                'recipe_id' => $recipe->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sorry, I encountered an error generating improvements. Please try again.'
            ], 500);
        }
    }
}
