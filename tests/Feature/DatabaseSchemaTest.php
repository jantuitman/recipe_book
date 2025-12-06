<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users table has unit preference columns with correct types and defaults.
     */
    public function test_users_table_has_unit_preference_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'volume_unit'));
        $this->assertTrue(Schema::hasColumn('users', 'weight_unit'));
        $this->assertTrue(Schema::hasColumn('users', 'time_format'));

        // Create a user and verify defaults
        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = DB::table('users')->where('email', 'test@example.com')->first();
        $this->assertEquals('ml', $user->volume_unit);
        $this->assertEquals('g', $user->weight_unit);
        $this->assertEquals('min', $user->time_format);
    }

    /**
     * Test that recipes table exists with correct structure.
     */
    public function test_recipes_table_exists_with_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('recipes'));
        $this->assertTrue(Schema::hasColumn('recipes', 'id'));
        $this->assertTrue(Schema::hasColumn('recipes', 'user_id'));
        $this->assertTrue(Schema::hasColumn('recipes', 'name'));
        $this->assertTrue(Schema::hasColumn('recipes', 'description'));
        $this->assertTrue(Schema::hasColumn('recipes', 'created_at'));
        $this->assertTrue(Schema::hasColumn('recipes', 'updated_at'));
    }

    /**
     * Test that recipes table has foreign key to users with cascade delete.
     */
    public function test_recipes_table_has_user_foreign_key_with_cascade(): void
    {
        // Create a user
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a recipe
        $recipeId = DB::table('recipes')->insertGetId([
            'user_id' => $userId,
            'name' => 'Test Recipe',
            'description' => 'A test recipe',
        ]);

        // Verify recipe exists
        $this->assertDatabaseHas('recipes', ['id' => $recipeId]);

        // Delete user - recipe should cascade delete
        DB::table('users')->where('id', $userId)->delete();

        // Verify recipe was deleted
        $this->assertDatabaseMissing('recipes', ['id' => $recipeId]);
    }

    /**
     * Test that recipe_versions table exists with correct structure.
     */
    public function test_recipe_versions_table_exists_with_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('recipe_versions'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'id'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'recipe_id'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'version_number'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'servings'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'ingredients'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'steps'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'change_summary'));
        $this->assertTrue(Schema::hasColumn('recipe_versions', 'created_at'));
    }

    /**
     * Test that recipe_versions servings column has default value of 4.
     */
    public function test_recipe_versions_servings_defaults_to_four(): void
    {
        // Create user and recipe
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $recipeId = DB::table('recipes')->insertGetId([
            'user_id' => $userId,
            'name' => 'Test Recipe',
        ]);

        // Create version without specifying servings
        $versionId = DB::table('recipe_versions')->insertGetId([
            'recipe_id' => $recipeId,
            'version_number' => 1,
            'ingredients' => json_encode([]),
            'steps' => json_encode([]),
            'created_at' => now(),
        ]);

        $version = DB::table('recipe_versions')->where('id', $versionId)->first();
        $this->assertEquals(4, $version->servings);
    }

    /**
     * Test that recipe_versions stores JSON in ingredients and steps columns.
     */
    public function test_recipe_versions_stores_json_data(): void
    {
        // Create user and recipe
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $recipeId = DB::table('recipes')->insertGetId([
            'user_id' => $userId,
            'name' => 'Test Recipe',
        ]);

        $ingredients = [
            ['name' => 'flour', 'quantity' => 250, 'unit' => 'g'],
            ['name' => 'eggs', 'quantity' => 2, 'unit' => 'pieces'],
        ];

        $steps = [
            ['step_number' => 1, 'instruction' => 'Mix ingredients'],
            ['step_number' => 2, 'instruction' => 'Bake at 180C'],
        ];

        // Create version with JSON data
        $versionId = DB::table('recipe_versions')->insertGetId([
            'recipe_id' => $recipeId,
            'version_number' => 1,
            'servings' => 6,
            'ingredients' => json_encode($ingredients),
            'steps' => json_encode($steps),
            'created_at' => now(),
        ]);

        $version = DB::table('recipe_versions')->where('id', $versionId)->first();
        $this->assertEquals($ingredients, json_decode($version->ingredients, true));
        $this->assertEquals($steps, json_decode($version->steps, true));
    }

    /**
     * Test that comments table exists with correct structure.
     */
    public function test_comments_table_exists_with_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('comments'));
        $this->assertTrue(Schema::hasColumn('comments', 'id'));
        $this->assertTrue(Schema::hasColumn('comments', 'recipe_version_id'));
        $this->assertTrue(Schema::hasColumn('comments', 'user_id'));
        $this->assertTrue(Schema::hasColumn('comments', 'content'));
        $this->assertTrue(Schema::hasColumn('comments', 'is_ai'));
        $this->assertTrue(Schema::hasColumn('comments', 'result_version_id'));
        $this->assertTrue(Schema::hasColumn('comments', 'created_at'));
    }

    /**
     * Test that comments user_id is nullable (for AI comments).
     */
    public function test_comments_user_id_is_nullable(): void
    {
        // Create user and recipe
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $recipeId = DB::table('recipes')->insertGetId([
            'user_id' => $userId,
            'name' => 'Test Recipe',
        ]);

        $versionId = DB::table('recipe_versions')->insertGetId([
            'recipe_id' => $recipeId,
            'version_number' => 1,
            'ingredients' => json_encode([]),
            'steps' => json_encode([]),
            'created_at' => now(),
        ]);

        // Create AI comment without user_id
        $commentId = DB::table('comments')->insertGetId([
            'recipe_version_id' => $versionId,
            'user_id' => null,
            'content' => 'AI generated comment',
            'is_ai' => true,
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('comments', ['id' => $commentId, 'user_id' => null]);
    }

    /**
     * Test that chat_messages table exists with correct structure.
     */
    public function test_chat_messages_table_exists_with_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('chat_messages'));
        $this->assertTrue(Schema::hasColumn('chat_messages', 'id'));
        $this->assertTrue(Schema::hasColumn('chat_messages', 'user_id'));
        $this->assertTrue(Schema::hasColumn('chat_messages', 'role'));
        $this->assertTrue(Schema::hasColumn('chat_messages', 'content'));
        $this->assertTrue(Schema::hasColumn('chat_messages', 'recipe_id'));
        $this->assertTrue(Schema::hasColumn('chat_messages', 'created_at'));
    }

    /**
     * Test that chat_messages recipe_id is nullable.
     */
    public function test_chat_messages_recipe_id_is_nullable(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create chat message without recipe_id
        $messageId = DB::table('chat_messages')->insertGetId([
            'user_id' => $userId,
            'role' => 'user',
            'content' => 'Hello, how do I make pasta?',
            'recipe_id' => null,
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('chat_messages', ['id' => $messageId, 'recipe_id' => null]);
    }

    /**
     * Test that chat_messages has foreign key to users with cascade delete.
     */
    public function test_chat_messages_cascade_deletes_with_user(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $messageId = DB::table('chat_messages')->insertGetId([
            'user_id' => $userId,
            'role' => 'user',
            'content' => 'Test message',
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('chat_messages', ['id' => $messageId]);

        // Delete user
        DB::table('users')->where('id', $userId)->delete();

        // Message should be deleted
        $this->assertDatabaseMissing('chat_messages', ['id' => $messageId]);
    }
}
