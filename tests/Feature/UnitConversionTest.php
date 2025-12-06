<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitConversionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function recipe_detail_page_includes_unit_conversion_controller()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $version = RecipeVersion::factory()->for($recipe)->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
                ['name' => 'milk', 'quantity' => 500, 'unit' => 'ml'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        // Unit conversion controller is present alongside serving-multiplier controller
        $response->assertSee('unit-conversion', false);
        $response->assertSee('data-controller=', false);
    }

    /** @test */
    public function ingredients_have_base_quantity_and_unit_data_attributes()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $version = RecipeVersion::factory()->for($recipe)->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('data-base-quantity="250"', false);
        $response->assertSee('data-base-unit="g"', false);
    }

    /** @test */
    public function unit_conversion_controller_receives_user_preferences()
    {
        $user = User::factory()->create([
            'volume_unit' => 'cups',
            'weight_unit' => 'oz',
            'time_format' => 'hr_min',
        ]);
        $recipe = Recipe::factory()->for($user)->create();
        $version = RecipeVersion::factory()->for($recipe)->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('data-unit-conversion-volume-unit-value="cups"', false);
        $response->assertSee('data-unit-conversion-weight-unit-value="oz"', false);
        $response->assertSee('data-unit-conversion-time-format-value="hr_min"', false);
    }

    /** @test */
    public function unit_conversion_defaults_to_metric_when_user_has_no_preferences()
    {
        // User model factory doesn't set preferences, so they should be null/default
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $version = RecipeVersion::factory()->for($recipe)->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        // Should default to metric
        $response->assertSee('data-unit-conversion-volume-unit-value="ml"', false);
        $response->assertSee('data-unit-conversion-weight-unit-value="g"', false);
        $response->assertSee('data-unit-conversion-time-format-value="min"', false);
    }

    /** @test */
    public function recipe_with_multiple_ingredient_types_includes_all_data_attributes()
    {
        $user = User::factory()->create([
            'volume_unit' => 'cups',
            'weight_unit' => 'oz',
        ]);
        $recipe = Recipe::factory()->for($user)->create();
        $version = RecipeVersion::factory()->for($recipe)->create([
            'version_number' => 1,
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],      // weight
                ['name' => 'milk', 'quantity' => 500, 'unit' => 'ml'],      // volume
                ['name' => 'eggs', 'quantity' => 2, 'unit' => 'pieces'],    // no conversion
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);

        // Weight ingredient
        $response->assertSee('data-base-quantity="250"', false);
        $response->assertSee('data-base-unit="g"', false);

        // Volume ingredient
        $response->assertSee('data-base-quantity="500"', false);
        $response->assertSee('data-base-unit="ml"', false);

        // Non-convertible ingredient
        $response->assertSee('data-base-quantity="2"', false);
        $response->assertSee('data-base-unit="pieces"', false);
    }
}
