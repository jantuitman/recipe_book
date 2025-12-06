<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\RecipeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_version_id' => RecipeVersion::factory(),
            'user_id' => User::factory(),
            'content' => fake()->sentence(),
            'is_ai' => false,
            'result_version_id' => null,
        ];
    }

    /**
     * Indicate that the comment is from AI.
     */
    public function ai(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_ai' => true,
            'user_id' => null,
        ]);
    }
}
