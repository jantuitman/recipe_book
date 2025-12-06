<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiRecipeParserTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_parser_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/ai/parse-recipe', [
            'text' => 'Some recipe text'
        ]);

        $response->assertStatus(401);
    }

    public function test_parser_endpoint_validates_text_input(): void
    {
        $user = User::factory()->create();

        // Missing text
        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('text');

        // Text too short
        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => 'short'
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('text');
    }

    public function test_parser_returns_structured_recipe_data(): void
    {
        $user = User::factory()->create();

        // Mock the OpenAiService
        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andReturn([
                'name' => 'Chocolate Chip Cookies',
                'servings' => 24,
                'ingredients' => [
                    ['name' => 'flour', 'quantity' => 280, 'unit' => 'g'],
                    ['name' => 'butter', 'quantity' => 227, 'unit' => 'g'],
                    ['name' => 'sugar', 'quantity' => 200, 'unit' => 'g'],
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Mix flour and butter'],
                    ['step_number' => 2, 'instruction' => 'Add sugar and mix well'],
                    ['step_number' => 3, 'instruction' => 'Bake for 12 minutes at 180°C'],
                ]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => '2 cups flour, 1 cup butter, 1 cup sugar. Mix and bake for 12 min at 350F. Makes 24 cookies.'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Chocolate Chip Cookies',
                'servings' => 24,
            ]
        ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('ingredients', $data);
        $this->assertArrayHasKey('steps', $data);
        $this->assertCount(3, $data['ingredients']);
        $this->assertCount(3, $data['steps']);

        // Verify ingredient structure
        $this->assertArrayHasKey('name', $data['ingredients'][0]);
        $this->assertArrayHasKey('quantity', $data['ingredients'][0]);
        $this->assertArrayHasKey('unit', $data['ingredients'][0]);

        // Verify steps structure
        $this->assertArrayHasKey('step_number', $data['steps'][0]);
        $this->assertArrayHasKey('instruction', $data['steps'][0]);
    }

    public function test_parser_converts_units_to_metric(): void
    {
        $user = User::factory()->create();

        // Mock response with metric units
        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andReturn([
                'name' => 'Test Recipe',
                'servings' => 4,
                'ingredients' => [
                    ['name' => 'flour', 'quantity' => 473, 'unit' => 'ml'],  // 2 cups
                    ['name' => 'butter', 'quantity' => 113, 'unit' => 'g'],  // 4 oz
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Mix ingredients'],
                    ['step_number' => 2, 'instruction' => 'Bake for 60 minutes'],  // 1 hour
                ]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => '2 cups flour, 4 oz butter. Mix and bake for 1 hour.'
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify metric units
        $this->assertEquals('ml', $data['ingredients'][0]['unit']);
        $this->assertEquals('g', $data['ingredients'][1]['unit']);
        $this->assertGreaterThan(400, $data['ingredients'][0]['quantity']); // 2 cups ≈ 473ml
        $this->assertGreaterThan(100, $data['ingredients'][1]['quantity']); // 4 oz ≈ 113g
    }

    public function test_parser_extracts_serving_count(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andReturn([
                'name' => 'Test Recipe',
                'servings' => 6,
                'ingredients' => [['name' => 'flour', 'quantity' => 100, 'unit' => 'g']],
                'steps' => [['step_number' => 1, 'instruction' => 'Mix']]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => 'Recipe for 6 people. 100g flour. Mix ingredients.'
        ]);

        $response->assertStatus(200);
        $this->assertEquals(6, $response->json('data.servings'));
    }

    public function test_parser_defaults_to_four_servings_when_not_specified(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andReturn([
                'name' => 'Test Recipe',
                'servings' => 4,  // Default
                'ingredients' => [['name' => 'flour', 'quantity' => 100, 'unit' => 'g']],
                'steps' => [['step_number' => 1, 'instruction' => 'Mix']]
            ]);

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => '100g flour. Mix ingredients.'
        ]);

        $response->assertStatus(200);
        $this->assertEquals(4, $response->json('data.servings'));
    }

    public function test_parser_handles_timeout_error(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andThrow(new \Exception('Request timed out'));

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => '100g flour. Mix ingredients.'
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'error' => 'The AI service took too long to respond. Please try again.'
        ]);
    }

    public function test_parser_handles_rate_limit_error(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andThrow(new \Exception('Rate limit exceeded (429)'));

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => '100g flour. Mix ingredients.'
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'error' => 'Too many requests. Please wait a moment and try again.'
        ]);
    }

    public function test_parser_handles_invalid_api_key_error(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andThrow(new \Exception('Unauthorized (401): Invalid API key'));

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => '100g flour. Mix ingredients.'
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'error' => 'AI service configuration error. Please contact support.'
        ]);
    }

    public function test_parser_handles_malformed_json_response(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OpenAiService::class);
        $mockService->shouldReceive('parseRecipeText')
            ->once()
            ->andThrow(new \Exception('Failed to parse AI response as JSON'));

        $this->app->instance(OpenAiService::class, $mockService);

        $response = $this->actingAs($user)->postJson('/ai/parse-recipe', [
            'text' => '100g flour. Mix ingredients.'
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'error' => 'Failed to understand the recipe format. Please try rephrasing your recipe.'
        ]);
    }
}
