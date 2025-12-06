<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_users_recipes(): void
    {
        // Create user with 5 recipes
        $user = User::factory()->create();

        $recipes = [];
        for ($i = 1; $i <= 5; $i++) {
            $recipe = $user->recipes()->create([
                'name' => "Recipe $i",
                'description' => "Description for recipe $i",
            ]);

            $recipe->versions()->create([
                'version_number' => 1,
                'servings' => 4,
                'ingredients' => [
                    ['name' => 'ingredient 1', 'quantity' => 100, 'unit' => 'g'],
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Step 1'],
                ],
                'change_summary' => 'Initial version',
            ]);

            $recipes[] = $recipe;
        }

        // Login and visit dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('My Recipes');

        // Verify all 5 recipes are displayed
        foreach ($recipes as $recipe) {
            $response->assertSee($recipe->name);
            $response->assertSee($recipe->description);
        }

        // Verify action buttons are present
        $response->assertSee('New Recipe');
        $response->assertSee('AI Chat');
    }

    public function test_dashboard_shows_empty_state_when_no_recipes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('No recipes yet');
        $response->assertSee('Start building your recipe collection');
        $response->assertSee('Create First Recipe');
    }

    public function test_dashboard_shows_recipe_version_numbers(): void
    {
        $user = User::factory()->create();

        $recipe = $user->recipes()->create([
            'name' => 'Test Recipe',
            'description' => 'Test Description',
        ]);

        $recipe->versions()->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [],
            'steps' => [],
            'change_summary' => 'Initial version',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Version 1');
    }

    public function test_user_can_only_see_their_own_recipes(): void
    {
        // Create two users with recipes
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $recipe1 = $user1->recipes()->create([
            'name' => 'User 1 Recipe',
            'description' => 'User 1 Description',
        ]);

        $recipe1->versions()->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [],
            'steps' => [],
        ]);

        $recipe2 = $user2->recipes()->create([
            'name' => 'User 2 Recipe',
            'description' => 'User 2 Description',
        ]);

        $recipe2->versions()->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [],
            'steps' => [],
        ]);

        // Login as user 1
        $response = $this->actingAs($user1)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('User 1 Recipe');
        $response->assertDontSee('User 2 Recipe');
    }

    public function test_user_cannot_access_another_users_recipe(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $recipe = $user2->recipes()->create([
            'name' => 'User 2 Recipe',
            'description' => 'User 2 Description',
        ]);

        $recipe->versions()->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [],
            'steps' => [],
        ]);

        // Login as user 1 and try to access user 2's recipe
        $response = $this->actingAs($user1)->get("/recipes/{$recipe->id}");

        // Should return 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_recipe_detail_page_displays_correctly(): void
    {
        $user = User::factory()->create();

        $recipe = $user->recipes()->create([
            'name' => 'Chocolate Cake',
            'description' => 'Delicious chocolate cake',
        ]);

        $recipe->versions()->create([
            'version_number' => 1,
            'servings' => 8,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 200, 'unit' => 'g'],
                ['name' => 'sugar', 'quantity' => 150, 'unit' => 'g'],
                ['name' => 'cocoa powder', 'quantity' => 50, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix dry ingredients'],
                ['step_number' => 2, 'instruction' => 'Add wet ingredients'],
                ['step_number' => 3, 'instruction' => 'Bake at 180°C for 30 minutes'],
            ],
            'change_summary' => 'Initial version',
        ]);

        $response = $this->actingAs($user)->get("/recipes/{$recipe->id}");

        $response->assertStatus(200);
        $response->assertSee('Chocolate Cake');
        $response->assertSee('Delicious chocolate cake');
        $response->assertSee('Version 1');
        $response->assertSee('Servings');
        $response->assertSee('8', false); // false = don't escape HTML

        // Check ingredients (displayed values may be converted based on user preferences)
        $response->assertSee('flour');
        $response->assertSee('sugar');
        $response->assertSee('cocoa powder');

        // Check steps
        $response->assertSee('Mix dry ingredients');
        $response->assertSee('Add wet ingredients');
        $response->assertSee('Bake at 180°C for 30 minutes');

        // Check action buttons
        $response->assertSee('Edit');
        $response->assertSee('Delete');
    }

    public function test_dashboard_includes_search_box(): void
    {
        $user = User::factory()->create();

        $recipe = $user->recipes()->create([
            'name' => 'Test Recipe',
            'description' => 'Test',
        ]);

        $recipe->versions()->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [],
            'steps' => [],
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Search recipes by name or ingredient');
    }

    public function test_recipe_cards_include_data_attributes_for_search(): void
    {
        $user = User::factory()->create();

        $recipe = $user->recipes()->create([
            'name' => 'Pasta Carbonara',
            'description' => 'Italian pasta dish',
        ]);

        $recipe->versions()->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'spaghetti', 'quantity' => 400, 'unit' => 'g'],
                ['name' => 'eggs', 'quantity' => 4, 'unit' => 'pieces'],
            ],
            'steps' => [],
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Check that data attributes are present for JavaScript search
        $content = $response->getContent();
        $this->assertStringContainsString('data-name="pasta carbonara"', $content);
        $this->assertStringContainsString('spaghetti', $content);
        $this->assertStringContainsString('eggs', $content);
    }
}
