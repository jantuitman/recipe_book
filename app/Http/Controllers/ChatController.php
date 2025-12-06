<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(private OpenAiService $openAiService)
    {
    }

    public function index()
    {
        return view('chat.index');
    }

    /**
     * Get chat message history for the authenticated user
     */
    public function messages()
    {
        $messages = Auth::user()
            ->chatMessages()
            ->chronological()
            ->get(['role', 'content', 'created_at']);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Send a message and get AI response
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:2000'
        ]);

        $user = Auth::user();
        $userMessage = $request->input('message');

        // Save user message
        $user->chatMessages()->create([
            'role' => 'user',
            'content' => $userMessage
        ]);

        try {
            // Get recent conversation history for context (last 10 messages, excluding the one we just saved)
            $recentMessages = $user->chatMessages()
                ->chronological()
                ->where('id', '!=', $user->chatMessages()->latest('id')->first()->id)
                ->take(10)
                ->get(['role', 'content'])
                ->map(fn($msg) => [
                    'role' => $msg->role,
                    'content' => $msg->content
                ])
                ->toArray();

            // Get AI response (with recipe detection)
            $aiResponse = $this->openAiService->chatResponse($userMessage, $recentMessages, detectRecipe: true);

            // Handle both string and array responses
            if (is_array($aiResponse) && isset($aiResponse['has_recipe']) && $aiResponse['has_recipe'] === true) {
                // Recipe detected in response
                $messageContent = $aiResponse['message'];
                $recipeData = $aiResponse['recipe'];

                // Save AI response with message only (not the JSON structure)
                $user->chatMessages()->create([
                    'role' => 'assistant',
                    'content' => $messageContent
                ]);

                return response()->json([
                    'success' => true,
                    'response' => $messageContent,
                    'has_recipe' => true,
                    'recipe' => $recipeData
                ]);
            } else {
                // Normal response (string)
                $messageContent = is_array($aiResponse) ? ($aiResponse['message'] ?? $aiResponse) : $aiResponse;

                // Save AI response
                $user->chatMessages()->create([
                    'role' => 'assistant',
                    'content' => $messageContent
                ]);

                return response()->json([
                    'success' => true,
                    'response' => $messageContent,
                    'has_recipe' => false
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Sorry, I encountered an error processing your message. Please try again.'
            ], 500);
        }
    }
}
