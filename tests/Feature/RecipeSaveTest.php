<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_can_be_saved_to_database(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Test Recipe',
            'description' => 'A test recipe',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
                ['name' => 'sugar', 'quantity' => 100, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
                ['step_number' => 2, 'instruction' => 'Bake for 30 minutes'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('recipes', [
            'name' => 'Test Recipe',
            'user_id' => $user->id,
        ]);
    }

    public function test_saved_recipe_has_proper_json_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'JSON Test Recipe',
            'description' => 'Testing JSON structure',
            'servings' => 6,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
                ['name' => 'water', 'quantity' => 200, 'unit' => 'ml'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'First step'],
                ['step_number' => 2, 'instruction' => 'Second step'],
            ],
        ]);

        $recipe = Recipe::where('name', 'JSON Test Recipe')->first();
        $this->assertNotNull($recipe);

        $version = $recipe->versions->first();
        $this->assertNotNull($version);

        // Check ingredients JSON structure
        $this->assertIsArray($version->ingredients);
        $this->assertCount(2, $version->ingredients);
        $this->assertEquals('flour', $version->ingredients[0]['name']);
        $this->assertEquals(250, $version->ingredients[0]['quantity']);
        $this->assertEquals('g', $version->ingredients[0]['unit']);

        // Check steps JSON structure
        $this->assertIsArray($version->steps);
        $this->assertCount(2, $version->steps);
        $this->assertEquals(1, $version->steps[0]['step_number']);
        $this->assertEquals('First step', $version->steps[0]['instruction']);
    }

    public function test_recipe_save_creates_initial_version(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Version Test',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'test ingredient', 'quantity' => 1, 'unit' => 'pieces'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Test step'],
            ],
        ]);

        $recipe = Recipe::where('name', 'Version Test')->first();
        $this->assertNotNull($recipe);

        $version = $recipe->versions->first();
        $this->assertNotNull($version);
        $this->assertEquals(1, $version->version_number);
        $this->assertEquals('Initial version', $version->change_summary);
        $this->assertEquals(4, $version->servings);
    }

    public function test_recipe_save_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix'],
            ],
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_recipe_save_requires_ingredients(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'No Ingredients Recipe',
            'servings' => 4,
            'ingredients' => [],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Do something'],
            ],
        ]);

        $response->assertSessionHasErrors('ingredients');
    }

    public function test_recipe_save_requires_steps(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'No Steps Recipe',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ],
            'steps' => [],
        ]);

        $response->assertSessionHasErrors('steps');
    }

    public function test_recipe_save_requires_valid_servings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Invalid Servings',
            'servings' => 0, // Invalid: must be at least 1
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix'],
            ],
        ]);

        $response->assertSessionHasErrors('servings');
    }

    public function test_recipe_save_validates_ingredient_structure(): void
    {
        $user = User::factory()->create();

        // Missing unit in ingredient
        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Invalid Ingredient',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250], // Missing unit
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix'],
            ],
        ]);

        $response->assertSessionHasErrors('ingredients.0.unit');
    }

    public function test_recipe_save_validates_steps_structure(): void
    {
        $user = User::factory()->create();

        // Missing instruction in step
        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Invalid Step',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1], // Missing instruction
            ],
        ]);

        $response->assertSessionHasErrors('steps.0.instruction');
    }
}
