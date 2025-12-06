<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_delete_their_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('recipes.destroy', $recipe));

        $response->assertRedirect(route('recipes.index'));
        $response->assertSessionHas('success', 'Recipe deleted successfully!');
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    public function test_user_cannot_delete_another_users_recipe(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)
            ->delete(route('recipes.destroy', $recipe));

        $response->assertStatus(403); // Forbidden
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id]);
    }

    public function test_guest_cannot_delete_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $response = $this->delete(route('recipes.destroy', $recipe));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id]);
    }

    public function test_deleting_recipe_cascades_to_versions(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        // Create 3 versions
        $version1 = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);
        $version2 = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
        ]);
        $version3 = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 3,
        ]);

        $this->actingAs($user)->delete(route('recipes.destroy', $recipe));

        // Verify recipe is deleted
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);

        // Verify all versions are deleted
        $this->assertDatabaseMissing('recipe_versions', ['id' => $version1->id]);
        $this->assertDatabaseMissing('recipe_versions', ['id' => $version2->id]);
        $this->assertDatabaseMissing('recipe_versions', ['id' => $version3->id]);
    }

    public function test_deleting_recipe_cascades_to_comments(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        // Create 5 comments
        $comment1 = Comment::factory()->create(['recipe_version_id' => $version->id, 'user_id' => $user->id]);
        $comment2 = Comment::factory()->create(['recipe_version_id' => $version->id, 'user_id' => $user->id]);
        $comment3 = Comment::factory()->create(['recipe_version_id' => $version->id, 'user_id' => $user->id]);
        $comment4 = Comment::factory()->create(['recipe_version_id' => $version->id, 'user_id' => $user->id]);
        $comment5 = Comment::factory()->create(['recipe_version_id' => $version->id, 'user_id' => $user->id]);

        $this->actingAs($user)->delete(route('recipes.destroy', $recipe));

        // Verify recipe is deleted
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);

        // Verify all comments are deleted
        $this->assertDatabaseMissing('comments', ['id' => $comment1->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment2->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment3->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment4->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment5->id]);
    }

    public function test_deleting_recipe_with_versions_and_comments_cascades_all(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        // Create 3 versions
        $version1 = RecipeVersion::factory()->create(['recipe_id' => $recipe->id, 'version_number' => 1]);
        $version2 = RecipeVersion::factory()->create(['recipe_id' => $recipe->id, 'version_number' => 2]);
        $version3 = RecipeVersion::factory()->create(['recipe_id' => $recipe->id, 'version_number' => 3]);

        // Create 2 comments per version (6 total)
        $comments = [];
        foreach ([$version1, $version2, $version3] as $version) {
            $comments[] = Comment::factory()->create(['recipe_version_id' => $version->id, 'user_id' => $user->id]);
            $comments[] = Comment::factory()->create(['recipe_version_id' => $version->id, 'user_id' => $user->id]);
        }

        $this->actingAs($user)->delete(route('recipes.destroy', $recipe));

        // Verify recipe deleted
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);

        // Verify all 3 versions deleted
        $this->assertDatabaseMissing('recipe_versions', ['id' => $version1->id]);
        $this->assertDatabaseMissing('recipe_versions', ['id' => $version2->id]);
        $this->assertDatabaseMissing('recipe_versions', ['id' => $version3->id]);

        // Verify all 6 comments deleted
        foreach ($comments as $comment) {
            $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        }
    }
}
