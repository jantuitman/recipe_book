<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Recipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Create 5 sample recipes
        $recipes = [
            [
                'name' => 'Classic Spaghetti Carbonara',
                'description' => 'A traditional Italian pasta dish with eggs, cheese, and pancetta',
                'servings' => 4,
                'ingredients' => [
                    ['name' => 'spaghetti', 'quantity' => 400, 'unit' => 'g'],
                    ['name' => 'pancetta', 'quantity' => 150, 'unit' => 'g'],
                    ['name' => 'eggs', 'quantity' => 4, 'unit' => 'pieces'],
                    ['name' => 'parmesan cheese', 'quantity' => 100, 'unit' => 'g'],
                    ['name' => 'black pepper', 'quantity' => 5, 'unit' => 'g'],
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Cook spaghetti in salted boiling water until al dente'],
                    ['step_number' => 2, 'instruction' => 'Fry pancetta until crispy'],
                    ['step_number' => 3, 'instruction' => 'Beat eggs with grated parmesan and black pepper'],
                    ['step_number' => 4, 'instruction' => 'Drain pasta and mix with pancetta'],
                    ['step_number' => 5, 'instruction' => 'Remove from heat and quickly stir in egg mixture'],
                ],
            ],
            [
                'name' => 'Homemade Pizza Margherita',
                'description' => 'Simple and delicious pizza with tomato, mozzarella, and basil',
                'servings' => 2,
                'ingredients' => [
                    ['name' => 'pizza dough', 'quantity' => 500, 'unit' => 'g'],
                    ['name' => 'tomato sauce', 'quantity' => 200, 'unit' => 'ml'],
                    ['name' => 'mozzarella cheese', 'quantity' => 250, 'unit' => 'g'],
                    ['name' => 'fresh basil', 'quantity' => 10, 'unit' => 'pieces'],
                    ['name' => 'olive oil', 'quantity' => 30, 'unit' => 'ml'],
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Preheat oven to 250°C'],
                    ['step_number' => 2, 'instruction' => 'Roll out pizza dough to desired thickness'],
                    ['step_number' => 3, 'instruction' => 'Spread tomato sauce evenly on dough'],
                    ['step_number' => 4, 'instruction' => 'Tear mozzarella and distribute on pizza'],
                    ['step_number' => 5, 'instruction' => 'Bake for 10-12 minutes until golden'],
                    ['step_number' => 6, 'instruction' => 'Top with fresh basil and drizzle with olive oil'],
                ],
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Fresh and crispy romaine lettuce with classic Caesar dressing',
                'servings' => 4,
                'ingredients' => [
                    ['name' => 'romaine lettuce', 'quantity' => 2, 'unit' => 'pieces'],
                    ['name' => 'croutons', 'quantity' => 150, 'unit' => 'g'],
                    ['name' => 'parmesan cheese', 'quantity' => 50, 'unit' => 'g'],
                    ['name' => 'Caesar dressing', 'quantity' => 150, 'unit' => 'ml'],
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Wash and tear romaine lettuce into bite-sized pieces'],
                    ['step_number' => 2, 'instruction' => 'Toss lettuce with Caesar dressing'],
                    ['step_number' => 3, 'instruction' => 'Add croutons and toss gently'],
                    ['step_number' => 4, 'instruction' => 'Top with shaved parmesan cheese'],
                ],
            ],
            [
                'name' => 'Chocolate Chip Cookies',
                'description' => 'Soft and chewy homemade cookies with chocolate chips',
                'servings' => 24,
                'ingredients' => [
                    ['name' => 'flour', 'quantity' => 280, 'unit' => 'g'],
                    ['name' => 'butter', 'quantity' => 200, 'unit' => 'g'],
                    ['name' => 'sugar', 'quantity' => 150, 'unit' => 'g'],
                    ['name' => 'brown sugar', 'quantity' => 150, 'unit' => 'g'],
                    ['name' => 'eggs', 'quantity' => 2, 'unit' => 'pieces'],
                    ['name' => 'chocolate chips', 'quantity' => 300, 'unit' => 'g'],
                    ['name' => 'vanilla extract', 'quantity' => 5, 'unit' => 'ml'],
                    ['name' => 'baking soda', 'quantity' => 5, 'unit' => 'g'],
                    ['name' => 'salt', 'quantity' => 3, 'unit' => 'g'],
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Preheat oven to 180°C'],
                    ['step_number' => 2, 'instruction' => 'Cream butter with both sugars until fluffy'],
                    ['step_number' => 3, 'instruction' => 'Beat in eggs and vanilla extract'],
                    ['step_number' => 4, 'instruction' => 'Mix flour, baking soda, and salt in separate bowl'],
                    ['step_number' => 5, 'instruction' => 'Gradually combine dry ingredients with wet ingredients'],
                    ['step_number' => 6, 'instruction' => 'Fold in chocolate chips'],
                    ['step_number' => 7, 'instruction' => 'Drop spoonfuls onto baking sheet'],
                    ['step_number' => 8, 'instruction' => 'Bake for 10-12 minutes until golden brown'],
                ],
            ],
            [
                'name' => 'Thai Green Curry',
                'description' => 'Aromatic and spicy Thai curry with vegetables and coconut milk',
                'servings' => 4,
                'ingredients' => [
                    ['name' => 'green curry paste', 'quantity' => 50, 'unit' => 'g'],
                    ['name' => 'coconut milk', 'quantity' => 400, 'unit' => 'ml'],
                    ['name' => 'chicken breast', 'quantity' => 500, 'unit' => 'g'],
                    ['name' => 'bamboo shoots', 'quantity' => 200, 'unit' => 'g'],
                    ['name' => 'bell peppers', 'quantity' => 2, 'unit' => 'pieces'],
                    ['name' => 'Thai basil', 'quantity' => 20, 'unit' => 'g'],
                    ['name' => 'fish sauce', 'quantity' => 30, 'unit' => 'ml'],
                    ['name' => 'vegetable oil', 'quantity' => 30, 'unit' => 'ml'],
                ],
                'steps' => [
                    ['step_number' => 1, 'instruction' => 'Heat oil in large pan over medium heat'],
                    ['step_number' => 2, 'instruction' => 'Fry curry paste for 2 minutes until fragrant'],
                    ['step_number' => 3, 'instruction' => 'Add coconut milk and bring to simmer'],
                    ['step_number' => 4, 'instruction' => 'Add chicken and cook for 10 minutes'],
                    ['step_number' => 5, 'instruction' => 'Add vegetables and cook for 5 minutes'],
                    ['step_number' => 6, 'instruction' => 'Season with fish sauce'],
                    ['step_number' => 7, 'instruction' => 'Garnish with Thai basil and serve with rice'],
                ],
            ],
        ];

        foreach ($recipes as $recipeData) {
            $recipe = $user->recipes()->create([
                'name' => $recipeData['name'],
                'description' => $recipeData['description'],
            ]);

            $recipe->versions()->create([
                'version_number' => 1,
                'servings' => $recipeData['servings'],
                'ingredients' => $recipeData['ingredients'],
                'steps' => $recipeData['steps'],
                'change_summary' => 'Initial version',
            ]);
        }

        $this->command->info('Created test user (test@example.com / password) with 5 recipes');
    }
}
