<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('recipes.edit', $recipe));

        $response->assertOk();
        $response->assertSee('Edit Recipe');
        $response->assertSee($recipe->name);
    }

    public function test_user_cannot_edit_another_users_recipe(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get(route('recipes.edit', $recipe));

        $response->assertStatus(403); // Forbidden
    }

    public function test_recipe_edit_creates_new_version(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        // Create initial version
        $version1 = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 2, 'unit' => 'cups'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
        ]);

        // Update the recipe
        $response = $this->actingAs($user)->put(route('recipes.update', $recipe), [
            'name' => $recipe->name,
            'description' => $recipe->description,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 3, 'unit' => 'cups'], // Changed quantity
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
            'change_summary' => 'Increased flour amount',
        ]);

        $response->assertRedirect(route('recipes.show', $recipe));

        // Check that version 2 was created
        $this->assertDatabaseHas('recipe_versions', [
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'change_summary' => 'Increased flour amount',
        ]);

        // Check that version 1 still exists
        $this->assertDatabaseHas('recipe_versions', [
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        // Verify recipe has 2 versions
        $recipe->refresh();
        $this->assertEquals(2, $recipe->versions()->count());
    }

    public function test_latest_version_is_updated_after_edit(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $this->actingAs($user)->put(route('recipes.update', $recipe), [
            'name' => $recipe->name,
            'description' => $recipe->description,
            'servings' => 6,
            'ingredients' => [
                ['name' => 'sugar', 'quantity' => 1, 'unit' => 'cup'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Add sugar'],
            ],
        ]);

        $recipe->refresh();
        $latestVersion = $recipe->versions->sortByDesc('version_number')->first();

        $this->assertEquals(2, $latestVersion->version_number);
        $this->assertEquals(6, $latestVersion->servings);
        $this->assertEquals('sugar', $latestVersion->ingredients[0]['name']);
    }

    public function test_edit_requires_name(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        RecipeVersion::factory()->create(['recipe_id' => $recipe->id, 'version_number' => 1]);

        $response = $this->actingAs($user)->put(route('recipes.update', $recipe), [
            'name' => '', // Missing name
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 2, 'unit' => 'cups'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix'],
            ],
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_edit_requires_ingredients(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        RecipeVersion::factory()->create(['recipe_id' => $recipe->id, 'version_number' => 1]);

        $response = $this->actingAs($user)->put(route('recipes.update', $recipe), [
            'name' => $recipe->name,
            'servings' => 4,
            'ingredients' => [], // Empty ingredients
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix'],
            ],
        ]);

        $response->assertSessionHasErrors('ingredients');
    }

    public function test_edit_requires_steps(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        RecipeVersion::factory()->create(['recipe_id' => $recipe->id, 'version_number' => 1]);

        $response = $this->actingAs($user)->put(route('recipes.update', $recipe), [
            'name' => $recipe->name,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 2, 'unit' => 'cups'],
            ],
            'steps' => [], // Empty steps
        ]);

        $response->assertSessionHasErrors('steps');
    }

    public function test_change_summary_defaults_to_manual_edit(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        RecipeVersion::factory()->create(['recipe_id' => $recipe->id, 'version_number' => 1]);

        $this->actingAs($user)->put(route('recipes.update', $recipe), [
            'name' => $recipe->name,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 2, 'unit' => 'cups'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix'],
            ],
            // No change_summary provided
        ]);

        $this->assertDatabaseHas('recipe_versions', [
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'change_summary' => 'Manual edit',
        ]);
    }
}
