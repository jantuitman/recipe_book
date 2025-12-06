<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecipeParserController extends Controller
{
    private OpenAiService $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    /**
     * Parse unstructured recipe text using AI
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function parse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|min:10|max:10000',
        ]);

        try {
            $result = $this->openAiService->parseRecipeText($validated['text']);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            // Return user-friendly error messages
            $message = $this->getUserFriendlyErrorMessage($e);

            return response()->json([
                'success' => false,
                'error' => $message,
            ], 500);
        }
    }

    /**
     * Convert exception to user-friendly message
     *
     * @param \Exception $e
     * @return string
     */
    private function getUserFriendlyErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        // Check for common error patterns
        if (str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
            return 'The AI service took too long to respond. Please try again.';
        }

        if (str_contains($message, '429') || str_contains($message, 'rate limit')) {
            return 'Too many requests. Please wait a moment and try again.';
        }

        if (str_contains($message, '401') || str_contains($message, 'unauthorized') || str_contains($message, 'api key')) {
            return 'AI service configuration error. Please contact support.';
        }

        if (str_contains($message, 'json') || str_contains($message, 'parse')) {
            return 'Failed to understand the recipe format. Please try rephrasing your recipe.';
        }

        // Generic error
        return 'Failed to parse the recipe. Please check your input and try again.';
    }
}
