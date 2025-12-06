<?php

namespace App\Services;

use OpenAI\Client;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    private $client;
    private LoggerInterface $logger;

    public function __construct($client = null, LoggerInterface $logger = null)
    {
        // Allow injection for testing, otherwise create default client
        // Accept either OpenAI\Client or any object with a chat() method (for testing)
        $this->client = $client ?? \OpenAI::client(config('services.openai.api_key'));
        $this->logger = $logger ?? Log::channel('single');
    }

    /**
     * Parse unstructured recipe text into structured data
     *
     * @param string $text Raw recipe text
     * @return array Contains: name, servings, ingredients[], steps[]
     * @throws \Exception
     */
    public function parseRecipeText(string $text): array
    {
        $this->logger->info('Parsing recipe text', ['length' => strlen($text)]);

        $prompt = $this->getRecipeParserPrompt($text);

        try {
            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $this->getRecipeParserSystemPrompt()],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $this->logger->info('Recipe parsing complete', ['response_length' => strlen($content)]);

            $result = json_decode($content, true);

            if (!$result) {
                throw new \Exception('Failed to parse AI response as JSON');
            }

            // Validate structure
            if (!isset($result['name'], $result['ingredients'], $result['steps'])) {
                throw new \Exception('AI response missing required fields');
            }

            // Ensure servings defaults to 4
            if (!isset($result['servings']) || !is_numeric($result['servings'])) {
                $result['servings'] = 4;
            }

            $this->logger->info('Recipe parsed successfully', [
                'name' => $result['name'],
                'servings' => $result['servings'],
                'ingredient_count' => count($result['ingredients']),
                'step_count' => count($result['steps'])
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Recipe parsing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('Failed to parse recipe: ' . $e->getMessage());
        }
    }

    /**
     * Generate a chat response
     *
     * @param string $userMessage The user's message
     * @param array $conversationHistory Previous messages in format [['role' => 'user', 'content' => '...'], ...]
     * @param bool $detectRecipe Whether to detect and extract recipe data from response
     * @return array|string Returns string for normal chat, array with 'message' and 'recipe' keys if recipe detected
     * @throws \Exception
     */
    public function chatResponse(string $userMessage, array $conversationHistory = [], bool $detectRecipe = true)
    {
        $this->logger->info('Generating chat response', [
            'message_length' => strlen($userMessage),
            'history_count' => count($conversationHistory),
            'detect_recipe' => $detectRecipe
        ]);

        try {
            $messages = [
                ['role' => 'system', 'content' => $this->getChatSystemPrompt($detectRecipe)]
            ];

            // Add conversation history (limit to last 10 messages for context window)
            $recentHistory = array_slice($conversationHistory, -10);
            $messages = array_merge($messages, $recentHistory);

            // Add current user message
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            $responseConfig = [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => $messages,
                'temperature' => 0.7,
            ];

            // If recipe detection is enabled, request JSON format
            if ($detectRecipe) {
                $responseConfig['response_format'] = ['type' => 'json_object'];
            }

            $response = $this->client->chat()->create($responseConfig);

            $content = $response->choices[0]->message->content;
            $this->logger->info('Chat response generated', ['response_length' => strlen($content)]);

            // If recipe detection is enabled, parse the JSON response
            if ($detectRecipe) {
                $parsed = json_decode($content, true);

                if (!$parsed || !isset($parsed['message'])) {
                    // Fallback: if JSON parsing fails, return as string
                    $this->logger->warning('Failed to parse chat JSON, returning as string');
                    return $content;
                }

                // Check if a recipe was detected
                if (isset($parsed['has_recipe']) && $parsed['has_recipe'] === true && isset($parsed['recipe'])) {
                    $this->logger->info('Recipe detected in chat response', [
                        'recipe_name' => $parsed['recipe']['name'] ?? 'unknown'
                    ]);

                    return [
                        'message' => $parsed['message'],
                        'has_recipe' => true,
                        'recipe' => $parsed['recipe']
                    ];
                }

                // No recipe detected, return just the message
                return $parsed['message'];
            }

            // Recipe detection disabled, return raw content
            return $content;

        } catch (\Exception $e) {
            $this->logger->error('Chat response failed', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to generate response: ' . $e->getMessage());
        }
    }

    /**
     * Improve a recipe based on user feedback
     *
     * @param array $currentRecipe Current recipe structure
     * @param string $feedback User feedback
     * @return array Contains: ingredients[], steps[], change_summary
     * @throws \Exception
     */
    public function improveRecipe(array $currentRecipe, string $feedback): array
    {
        $this->logger->info('Improving recipe', [
            'recipe_name' => $currentRecipe['name'] ?? 'unknown',
            'feedback_length' => strlen($feedback)
        ]);

        try {
            $prompt = $this->getRecipeImprovementPrompt($currentRecipe, $feedback);

            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $this->getRecipeImprovementSystemPrompt()],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $result = json_decode($content, true);

            if (!$result || !isset($result['ingredients'], $result['steps'], $result['change_summary'])) {
                throw new \Exception('AI response missing required fields');
            }

            $this->logger->info('Recipe improvement complete', [
                'changes' => $result['change_summary']
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Recipe improvement failed', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to improve recipe: ' . $e->getMessage());
        }
    }

    // System prompts and prompt templates

    private function getRecipeParserSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a recipe parsing assistant. Your task is to extract structured recipe information from unstructured text.

CRITICAL REQUIREMENTS:
1. Convert ALL measurements to metric units:
   - Volumes to milliliters (ml): 1 cup = 237ml, 1 tbsp = 15ml, 1 tsp = 5ml, 1 fl oz = 30ml
   - Weights to grams (g): 1 oz = 28g, 1 lb = 454g
   - Times to minutes: 1 hour = 60 min
2. Extract serving size if mentioned (default to 4 if not specified)
3. Return only valid JSON with this exact structure:
{
  "name": "Recipe Name",
  "servings": 4,
  "ingredients": [
    {"name": "ingredient name", "quantity": 237, "unit": "ml"},
    {"name": "ingredient name", "quantity": 100, "unit": "g"}
  ],
  "steps": [
    {"step_number": 1, "instruction": "First step"},
    {"step_number": 2, "instruction": "Second step"}
  ]
}

Rules:
- Use metric units ONLY (ml, L, g, kg, min)
- If quantity is unclear, estimate reasonably
- For "a pinch", use quantity: 1, unit: "pinch"
- For countable items like eggs, use unit: "pieces"
- Number steps sequentially starting from 1
- If no recipe name is provided, suggest a descriptive name
PROMPT;
    }

    private function getRecipeParserPrompt(string $text): string
    {
        return "Parse this recipe text and return the structured JSON:\n\n" . $text;
    }

    private function getChatSystemPrompt(bool $detectRecipe = false): string
    {
        if ($detectRecipe) {
            return <<<'PROMPT'
You are a friendly AI cooking assistant for the "AI Recipe Book" application.

Your role:
- Help users with cooking questions, recipe ideas, and culinary techniques
- Suggest recipes based on ingredients or preferences
- Provide cooking tips and substitution ideas
- Answer questions about food preparation, storage, and safety

IMPORTANT: You must ALWAYS respond in JSON format with this structure:
{
  "message": "Your conversational response to the user",
  "has_recipe": false
}

When you suggest a COMPLETE recipe with ingredients and cooking steps, set "has_recipe" to true and include the recipe data:
{
  "message": "Here's a delicious pasta recipe for you! [Include your conversational intro here]",
  "has_recipe": true,
  "recipe": {
    "name": "Recipe Name",
    "servings": 4,
    "ingredients": [
      {"name": "ingredient name", "quantity": 250, "unit": "g"},
      {"name": "ingredient name", "quantity": 500, "unit": "ml"}
    ],
    "steps": [
      {"step_number": 1, "instruction": "First step description"},
      {"step_number": 2, "instruction": "Second step description"}
    ]
  }
}

Recipe data requirements:
- Use METRIC units only: ml, L, g, kg, min
- Convert: 1 cup=237ml, 1 tbsp=15ml, 1 tsp=5ml, 1 oz=28g, 1 lb=454g
- Default servings to 4 if not specified
- Only include recipe data for COMPLETE recipes (not ingredient lists or cooking tips alone)

Stay focused on cooking and food-related topics.
PROMPT;
        }

        return <<<'PROMPT'
You are a friendly AI cooking assistant for the "AI Recipe Book" application.

Your role:
- Help users with cooking questions, recipe ideas, and culinary techniques
- Suggest recipes based on ingredients or preferences
- Provide cooking tips and substitution ideas
- Answer questions about food preparation, storage, and safety
- When suggesting a complete recipe, format it clearly with ingredients and steps

Stay focused on cooking and food-related topics. Be concise but helpful.
PROMPT;
    }

    private function getRecipeImprovementSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a recipe improvement assistant. Based on user feedback, modify recipes to address their concerns.

Rules:
1. Keep measurements in metric (ml, g, min)
2. Make specific, actionable changes
3. Provide a clear summary of what changed
4. Return only valid JSON with this structure:
{
  "ingredients": [...same structure as input...],
  "steps": [...same structure as input...],
  "change_summary": "Brief description of changes made"
}
PROMPT;
    }

    private function getRecipeImprovementPrompt(array $currentRecipe, string $feedback): string
    {
        $recipeJson = json_encode($currentRecipe, JSON_PRETTY_PRINT);
        return <<<PROMPT
Current recipe:
{$recipeJson}

User feedback:
{$feedback}

Modify the recipe to address the feedback. Return the updated ingredients, steps, and a summary of changes.
PROMPT;
    }
}
