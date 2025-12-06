<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ChatMessage;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatRecipeSaveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function recipe_created_from_chat_links_to_message(): void
    {
        $user = User::factory()->create();

        // Create a chat message with a recipe suggestion
        $chatMessage = $user->chatMessages()->create([
            'role' => 'assistant',
            'content' => 'Here is a simple pasta recipe...',
        ]);

        // Create recipe with chat_message_id
        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Simple Pasta',
            'description' => 'A basic pasta recipe',
            'servings' => 2,
            'ingredients' => [
                ['name' => 'pasta', 'quantity' => 200, 'unit' => 'g'],
                ['name' => 'tomato sauce', 'quantity' => 100, 'unit' => 'ml'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Boil pasta'],
                ['step_number' => 2, 'instruction' => 'Add sauce'],
            ],
            'chat_message_id' => $chatMessage->id,
        ]);

        // Verify recipe was created
        $this->assertDatabaseHas('recipes', [
            'name' => 'Simple Pasta',
            'user_id' => $user->id,
        ]);

        // Verify chat message was linked to recipe
        $chatMessage->refresh();
        $this->assertNotNull($chatMessage->recipe_id);
        $this->assertEquals('Simple Pasta', $chatMessage->recipe->name);

        // Verify redirect
        $response->assertRedirect();
    }

    /** @test */
    public function recipe_can_be_created_without_chat_message_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Manual Recipe',
            'description' => '',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 500, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
        ]);

        $this->assertDatabaseHas('recipes', [
            'name' => 'Manual Recipe',
            'user_id' => $user->id,
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function user_cannot_link_recipe_to_another_users_chat_message(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 creates a chat message
        $chatMessage = $user1->chatMessages()->create([
            'role' => 'assistant',
            'content' => 'Here is a recipe...',
        ]);

        // User 2 tries to create a recipe linked to user 1's message
        $this->actingAs($user2)->post('/recipes', [
            'name' => 'Stolen Recipe',
            'description' => '',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 500, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
            'chat_message_id' => $chatMessage->id,
        ]);

        // Recipe should be created but chat message should NOT be linked
        $this->assertDatabaseHas('recipes', [
            'name' => 'Stolen Recipe',
            'user_id' => $user2->id,
        ]);

        $chatMessage->refresh();
        $this->assertNull($chatMessage->recipe_id);
    }

    /** @test */
    public function chat_message_recipe_id_is_updated_when_recipe_saved(): void
    {
        $user = User::factory()->create();

        $chatMessage = $user->chatMessages()->create([
            'role' => 'assistant',
            'content' => 'Try this pancake recipe...',
        ]);

        $this->assertNull($chatMessage->recipe_id);

        $this->actingAs($user)->post('/recipes', [
            'name' => 'Pancakes',
            'description' => '',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
                ['name' => 'milk', 'quantity' => 300, 'unit' => 'ml'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix flour and milk'],
                ['step_number' => 2, 'instruction' => 'Cook on griddle'],
            ],
            'chat_message_id' => $chatMessage->id,
        ]);

        $chatMessage->refresh();
        $this->assertNotNull($chatMessage->recipe_id);

        $recipe = Recipe::where('name', 'Pancakes')->first();
        $this->assertEquals($recipe->id, $chatMessage->recipe_id);
    }

    /** @test */
    public function validation_fails_with_invalid_chat_message_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recipes', [
            'name' => 'Test Recipe',
            'description' => '',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 500, 'unit' => 'g'],
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ],
            'chat_message_id' => 99999, // Non-existent ID
        ]);

        $response->assertSessionHasErrors('chat_message_id');
    }
}
