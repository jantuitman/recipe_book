<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeVersion>
 */
class RecipeVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'version_number' => 1,
            'servings' => fake()->numberBetween(1, 12),
            'ingredients' => [
                [
                    'name' => 'flour',
                    'quantity' => 500,
                    'unit' => 'g',
                ],
                [
                    'name' => 'sugar',
                    'quantity' => 200,
                    'unit' => 'g',
                ],
                [
                    'name' => 'eggs',
                    'quantity' => 2,
                    'unit' => 'pieces',
                ],
            ],
            'steps' => [
                [
                    'step_number' => 1,
                    'instruction' => 'Mix dry ingredients together',
                ],
                [
                    'step_number' => 2,
                    'instruction' => 'Add wet ingredients and stir until combined',
                ],
                [
                    'step_number' => 3,
                    'instruction' => 'Bake for 30 minutes',
                ],
            ],
            'change_summary' => null,
        ];
    }
}
