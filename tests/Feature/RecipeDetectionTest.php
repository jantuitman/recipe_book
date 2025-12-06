<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeDetectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function chat_detects_recipe_in_ai_response()
    {
        $user = User::factory()->create();

        // Mock OpenAI service to return recipe
        $mockService = $this->createMock(OpenAiService::class);
        $mockService->method('chatResponse')
            ->willReturn([
                'message' => "Here's a simple pasta recipe for you!",
                'has_recipe' => true,
                'recipe' => [
                    'name' => 'Simple Pasta',
                    'servings' => 4,
                    'ingredients' => [
                        ['name' => 'pasta', 'quantity' => 400, 'unit' => 'g'],
                        ['name' => 'olive oil', 'quantity' => 30, 'unit' => 'ml'],
                    ],
                    'steps' => [
                        ['step_number' => 1, 'instruction' => 'Boil water'],
                        ['step_number' => 2, 'instruction' => 'Cook pasta'],
                    ]
                ]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'Give me a pasta recipe'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_recipe' => true
            ]);

        $data = $response->json();
        $this->assertTrue($data['has_recipe']);
        $this->assertArrayHasKey('recipe', $data);
        $this->assertEquals('Simple Pasta', $data['recipe']['name']);
        $this->assertCount(2, $data['recipe']['ingredients']);
        $this->assertCount(2, $data['recipe']['steps']);
    }

    /** @test */
    public function chat_response_without_recipe_has_no_recipe_flag()
    {
        $user = User::factory()->create();

        // Mock OpenAI service to return normal chat response
        $mockService = $this->createMock(OpenAiService::class);
        $mockService->method('chatResponse')
            ->willReturn("That's a great question about cooking!");

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'How do I store tomatoes?'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_recipe' => false
            ]);

        $data = $response->json();
        $this->assertFalse($data['has_recipe']);
        $this->assertArrayNotHasKey('recipe', $data);
    }

    /** @test */
    public function recipe_data_is_properly_structured()
    {
        $user = User::factory()->create();

        // Mock OpenAI service to return recipe with full structure
        $mockService = $this->createMock(OpenAiService::class);
        $mockService->method('chatResponse')
            ->willReturn([
                'message' => "Here's a chocolate cake recipe!",
                'has_recipe' => true,
                'recipe' => [
                    'name' => 'Chocolate Cake',
                    'servings' => 8,
                    'ingredients' => [
                        ['name' => 'flour', 'quantity' => 300, 'unit' => 'g'],
                        ['name' => 'sugar', 'quantity' => 200, 'unit' => 'g'],
                        ['name' => 'cocoa', 'quantity' => 50, 'unit' => 'g'],
                    ],
                    'steps' => [
                        ['step_number' => 1, 'instruction' => 'Mix dry ingredients'],
                        ['step_number' => 2, 'instruction' => 'Add wet ingredients'],
                        ['step_number' => 3, 'instruction' => 'Bake at 180C for 30 minutes'],
                    ]
                ]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'I want a chocolate cake recipe'
        ]);

        $response->assertStatus(200);
        $recipe = $response->json('recipe');

        // Verify recipe structure
        $this->assertArrayHasKey('name', $recipe);
        $this->assertArrayHasKey('servings', $recipe);
        $this->assertArrayHasKey('ingredients', $recipe);
        $this->assertArrayHasKey('steps', $recipe);

        // Verify ingredient structure
        $this->assertIsArray($recipe['ingredients']);
        foreach ($recipe['ingredients'] as $ingredient) {
            $this->assertArrayHasKey('name', $ingredient);
            $this->assertArrayHasKey('quantity', $ingredient);
            $this->assertArrayHasKey('unit', $ingredient);
        }

        // Verify step structure
        $this->assertIsArray($recipe['steps']);
        foreach ($recipe['steps'] as $step) {
            $this->assertArrayHasKey('step_number', $step);
            $this->assertArrayHasKey('instruction', $step);
        }
    }

    /** @test */
    public function recipe_uses_metric_units()
    {
        $user = User::factory()->create();

        // Mock OpenAI service to return recipe with metric units
        $mockService = $this->createMock(OpenAiService::class);
        $mockService->method('chatResponse')
            ->willReturn([
                'message' => "Here's the recipe!",
                'has_recipe' => true,
                'recipe' => [
                    'name' => 'Test Recipe',
                    'servings' => 4,
                    'ingredients' => [
                        ['name' => 'water', 'quantity' => 500, 'unit' => 'ml'],
                        ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
                    ],
                    'steps' => [
                        ['step_number' => 1, 'instruction' => 'Mix'],
                    ]
                ]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'Recipe please'
        ]);

        $response->assertStatus(200);
        $recipe = $response->json('recipe');

        // Verify metric units are used
        foreach ($recipe['ingredients'] as $ingredient) {
            $unit = $ingredient['unit'];
            $this->assertContains($unit, ['ml', 'L', 'g', 'kg', 'min', 'pieces', 'pinch']);
        }
    }

    /** @test */
    public function chat_saves_only_message_content_not_json_structure()
    {
        $user = User::factory()->create();

        $messageText = "Here's a delicious pasta recipe!";

        // Mock OpenAI service
        $mockService = $this->createMock(OpenAiService::class);
        $mockService->method('chatResponse')
            ->willReturn([
                'message' => $messageText,
                'has_recipe' => true,
                'recipe' => [
                    'name' => 'Pasta',
                    'servings' => 4,
                    'ingredients' => [['name' => 'pasta', 'quantity' => 400, 'unit' => 'g']],
                    'steps' => [['step_number' => 1, 'instruction' => 'Cook']]
                ]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $this->actingAs($user)->postJson('/chat', [
            'message' => 'Give me a recipe'
        ]);

        // Verify the saved chat message contains only the message text
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => $messageText
        ]);

        // Verify it doesn't contain JSON structure
        $savedMessage = $user->chatMessages()->where('role', 'assistant')->first();
        $this->assertStringNotContainsString('"has_recipe"', $savedMessage->content);
        $this->assertStringNotContainsString('"recipe"', $savedMessage->content);
    }
}
