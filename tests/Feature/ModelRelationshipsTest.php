<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\Comment;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User model has recipes relationship.
     */
    public function test_user_has_recipes_relationship(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::create([
            'user_id' => $user->id,
            'name' => 'Test Recipe',
            'description' => 'Test description',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->recipes);
        $this->assertTrue($user->recipes->contains($recipe));
        $this->assertEquals(1, $user->recipes->count());
    }

    /**
     * Test User model has comments relationship.
     */
    public function test_user_has_comments_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);
        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $comment = Comment::create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'Test comment',
            'is_ai' => false,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->comments);
        $this->assertTrue($user->comments->contains($comment));
    }

    /**
     * Test User model has chatMessages relationship.
     */
    public function test_user_has_chat_messages_relationship(): void
    {
        $user = User::factory()->create();

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'Hello',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->chatMessages);
        $this->assertTrue($user->chatMessages->contains($message));
    }

    /**
     * Test Recipe model has user relationship.
     */
    public function test_recipe_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'user_id' => $user->id,
            'name' => 'Test Recipe',
        ]);

        $this->assertInstanceOf(User::class, $recipe->user);
        $this->assertEquals($user->id, $recipe->user->id);
    }

    /**
     * Test Recipe model has versions relationship.
     */
    public function test_recipe_has_versions_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);

        $version1 = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $version2 = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'ingredients' => [],
            'steps' => [],
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $recipe->versions);
        $this->assertEquals(2, $recipe->versions->count());
    }

    /**
     * Test Recipe model latestVersion returns highest version number.
     */
    public function test_recipe_latest_version_returns_highest_version(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);

        RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $latestVersion = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'ingredients' => [],
            'steps' => [],
        ]);

        $recipe->load('latestVersion');
        $this->assertInstanceOf(RecipeVersion::class, $recipe->latestVersion);
        $this->assertEquals(2, $recipe->latestVersion->version_number);
        $this->assertEquals($latestVersion->id, $recipe->latestVersion->id);
    }

    /**
     * Test RecipeVersion model has recipe relationship.
     */
    public function test_recipe_version_has_recipe_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);

        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $this->assertInstanceOf(Recipe::class, $version->recipe);
        $this->assertEquals($recipe->id, $version->recipe->id);
    }

    /**
     * Test RecipeVersion model has comments relationship.
     */
    public function test_recipe_version_has_comments_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);
        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $comment = Comment::create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'Test comment',
            'is_ai' => false,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $version->comments);
        $this->assertTrue($version->comments->contains($comment));
    }

    /**
     * Test RecipeVersion ingredients are cast to array.
     */
    public function test_recipe_version_ingredients_cast_to_array(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);

        $ingredients = [
            ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ['name' => 'eggs', 'quantity' => 2, 'unit' => 'pieces'],
        ];

        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => $ingredients,
            'steps' => [],
        ]);

        // Reload from database
        $version = RecipeVersion::find($version->id);

        $this->assertIsArray($version->ingredients);
        $this->assertEquals($ingredients, $version->ingredients);
    }

    /**
     * Test RecipeVersion steps are cast to array.
     */
    public function test_recipe_version_steps_cast_to_array(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);

        $steps = [
            ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ['step_number' => 2, 'instruction' => 'Bake at 180C'],
        ];

        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => $steps,
        ]);

        // Reload from database
        $version = RecipeVersion::find($version->id);

        $this->assertIsArray($version->steps);
        $this->assertEquals($steps, $version->steps);
    }

    /**
     * Test Comment model has recipeVersion relationship.
     */
    public function test_comment_has_recipe_version_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);
        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $comment = Comment::create([
            'recipe_version_id' => $version->id,
            'content' => 'Test comment',
            'is_ai' => false,
        ]);

        $this->assertInstanceOf(RecipeVersion::class, $comment->recipeVersion);
        $this->assertEquals($version->id, $comment->recipeVersion->id);
    }

    /**
     * Test Comment model has user relationship.
     */
    public function test_comment_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);
        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $comment = Comment::create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'Test comment',
            'is_ai' => false,
        ]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->id, $comment->user->id);
    }

    /**
     * Test Comment model has resultVersion relationship.
     */
    public function test_comment_has_result_version_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);

        $version1 = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        $version2 = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
            'ingredients' => [],
            'steps' => [],
        ]);

        // AI comment that resulted in version 2
        $comment = Comment::create([
            'recipe_version_id' => $version1->id,
            'content' => 'AI suggested improvements',
            'is_ai' => true,
            'result_version_id' => $version2->id,
        ]);

        $this->assertInstanceOf(RecipeVersion::class, $comment->resultVersion);
        $this->assertEquals($version2->id, $comment->resultVersion->id);
    }

    /**
     * Test Comment model isAi scope filters AI comments.
     */
    public function test_comment_is_ai_scope(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);
        $version = RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
            'ingredients' => [],
            'steps' => [],
        ]);

        Comment::create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'User comment',
            'is_ai' => false,
        ]);

        Comment::create([
            'recipe_version_id' => $version->id,
            'content' => 'AI comment',
            'is_ai' => true,
        ]);

        $aiComments = Comment::isAi()->get();
        $this->assertEquals(1, $aiComments->count());
        $this->assertTrue($aiComments->first()->is_ai);
    }

    /**
     * Test ChatMessage model has user relationship.
     */
    public function test_chat_message_has_user_relationship(): void
    {
        $user = User::factory()->create();

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'Hello',
        ]);

        $this->assertInstanceOf(User::class, $message->user);
        $this->assertEquals($user->id, $message->user->id);
    }

    /**
     * Test ChatMessage model has recipe relationship.
     */
    public function test_chat_message_has_recipe_relationship(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['user_id' => $user->id, 'name' => 'Test']);

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => 'Here is your recipe',
            'recipe_id' => $recipe->id,
        ]);

        $this->assertInstanceOf(Recipe::class, $message->recipe);
        $this->assertEquals($recipe->id, $message->recipe->id);
    }

    /**
     * Test ChatMessage chronological scope.
     */
    public function test_chat_message_chronological_scope(): void
    {
        $user = User::factory()->create();

        // Create messages in reverse order
        sleep(1);
        $message1 = ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'First',
        ]);

        sleep(1);
        $message2 = ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => 'Second',
        ]);

        $messages = ChatMessage::chronological()->get();
        $this->assertEquals('First', $messages->first()->content);
        $this->assertEquals('Second', $messages->last()->content);
    }
}
