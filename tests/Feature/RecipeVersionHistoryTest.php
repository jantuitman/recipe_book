<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeVersionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_version_history(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        // Create multiple versions
        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'change_summary' => 'Initial version',
        ]);
        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'change_summary' => 'Added more salt',
        ]);
        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 3,
            'change_summary' => 'Changed cooking time',
        ]);

        $response = $this->actingAs($user)->get(route('recipes.history', $recipe));

        $response->assertStatus(200);
        $response->assertSee('Version History');
        $response->assertSee('Version 1');
        $response->assertSee('Version 2');
        $response->assertSee('Version 3');
    }

    public function test_version_history_shows_change_summaries(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'change_summary' => 'Initial version',
        ]);
        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'change_summary' => 'Reduced sugar by half',
        ]);

        $response = $this->actingAs($user)->get(route('recipes.history', $recipe));

        $response->assertSee('Initial version');
        $response->assertSee('Reduced sugar by half');
    }

    public function test_version_history_shows_current_version_indicator(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);
        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('recipes.history', $recipe));

        $response->assertSee('Current');
    }

    public function test_user_cannot_view_another_users_recipe_history(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get(route('recipes.history', $recipe));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_recipe_history(): void
    {
        $recipe = Recipe::factory()->create();

        $response = $this->get(route('recipes.history', $recipe));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_specific_version(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'change_summary' => 'Updated ingredients',
        ]);

        $response = $this->actingAs($user)->get(route('recipes.versions.show', [$recipe, $version]));

        $response->assertStatus(200);
        $response->assertSee('Version 2');
        $response->assertSee('Updated ingredients');
        $response->assertSee('You are viewing an older version');
    }

    public function test_specific_version_displays_correct_ingredients(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $ingredients = [
            ['name' => 'flour', 'quantity' => 500, 'unit' => 'g'],
            ['name' => 'sugar', 'quantity' => 200, 'unit' => 'g'],
        ];

        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => $ingredients,
        ]);

        $response = $this->actingAs($user)->get(route('recipes.versions.show', [$recipe, $version]));

        $response->assertSee('500');
        $response->assertSee('flour');
        $response->assertSee('200');
        $response->assertSee('sugar');
    }

    public function test_specific_version_displays_correct_steps(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $steps = [
            ['step_number' => 1, 'instruction' => 'Mix flour and sugar'],
            ['step_number' => 2, 'instruction' => 'Add eggs'],
        ];

        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'steps' => $steps,
        ]);

        $response = $this->actingAs($user)->get(route('recipes.versions.show', [$recipe, $version]));

        $response->assertSee('Mix flour and sugar');
        $response->assertSee('Add eggs');
    }

    public function test_version_must_belong_to_recipe(): void
    {
        $user = User::factory()->create();
        $recipe1 = Recipe::factory()->create(['user_id' => $user->id]);
        $recipe2 = Recipe::factory()->create(['user_id' => $user->id]);

        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe2->id,
            'version_number' => 1,
        ]);

        // Try to access recipe1 with recipe2's version
        $response = $this->actingAs($user)->get(route('recipes.versions.show', [$recipe1, $version]));

        $response->assertStatus(404);
    }

    public function test_user_cannot_view_another_users_recipe_version(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user1->id]);

        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($user2)->get(route('recipes.versions.show', [$recipe, $version]));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_specific_version(): void
    {
        $recipe = Recipe::factory()->create();
        $version = RecipeVersion::factory()->create(['recipe_id' => $recipe->id]);

        $response = $this->get(route('recipes.versions.show', [$recipe, $version]));

        $response->assertRedirect(route('login'));
    }
}
