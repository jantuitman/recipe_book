<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_detail_page_displays_comments(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        // Create 3 comments
        Comment::factory()->count(3)->create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'is_ai' => false,
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('Comments');
        // Verify all comments are displayed
        $comments = Comment::where('recipe_version_id', $version->id)->get();
        foreach ($comments as $comment) {
            $response->assertSee($comment->content);
            $response->assertSee($user->name);
        }
    }

    public function test_comments_show_user_name_and_date(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $comment = Comment::factory()->create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'is_ai' => false,
            'content' => 'Great recipe!',
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Great recipe!');
        // Check for relative time (created_at->diffForHumans())
        $response->assertSee('ago');
    }

    public function test_comments_are_in_chronological_order(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        // Create comments with specific timestamps
        $comment1 = Comment::factory()->create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'First comment',
            'created_at' => now()->subHours(2),
        ]);

        $comment2 = Comment::factory()->create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'Second comment',
            'created_at' => now()->subHour(),
        ]);

        $comment3 = Comment::factory()->create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'Third comment',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);

        // Verify order in HTML
        $content = $response->content();
        $pos1 = strpos($content, 'First comment');
        $pos2 = strpos($content, 'Second comment');
        $pos3 = strpos($content, 'Third comment');

        $this->assertTrue($pos1 < $pos2 && $pos2 < $pos3, 'Comments should be in chronological order');
    }

    public function test_ai_comments_show_ai_assistant_as_author(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        Comment::factory()->create([
            'recipe_version_id' => $version->id,
            'user_id' => null,
            'is_ai' => true,
            'content' => 'AI suggestion here',
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('AI Assistant');
        $response->assertSee('AI suggestion here');
    }

    public function test_ai_comments_have_different_styling(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        Comment::factory()->create([
            'recipe_version_id' => $version->id,
            'user_id' => null,
            'is_ai' => true,
            'content' => 'AI comment content',
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        // Check for AI-specific styling class
        $response->assertSee('border-l-4 border-[#81B29A]');
        // Check for AI icon SVG
        $response->assertSee('text-[#81B29A]');
    }

    public function test_ai_comments_with_result_version_show_link(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $version2 = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 2,
        ]);

        $version3 = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 3,
        ]);

        // Create AI comment on version 2 that links to version 3
        Comment::factory()->create([
            'recipe_version_id' => $version2->id,
            'user_id' => null,
            'is_ai' => true,
            'content' => 'I adjusted the salt amount',
            'result_version_id' => $version3->id,
        ]);

        // View version 2 to see the comment with link
        $response = $this->actingAs($user)->get(route('recipes.versions.show', [$recipe, $version2]));

        $response->assertStatus(200);
        $response->assertSee('This feedback led to Version 3');
        $response->assertSee(route('recipes.versions.show', [$recipe, $version3]));
    }

    public function test_user_can_add_comment_to_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($user)->post(
            route('comments.store', [$recipe, $version]),
            ['content' => 'This is a test comment']
        );

        $response->assertRedirect(route('recipes.show', $recipe));
        $response->assertSessionHas('success', 'Comment added successfully!');

        $this->assertDatabaseHas('comments', [
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'content' => 'This is a test comment',
            'is_ai' => false,
        ]);
    }

    public function test_comment_appears_in_list_after_submission(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $this->actingAs($user)->post(
            route('comments.store', [$recipe, $version]),
            ['content' => 'My new comment']
        );

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('My new comment');
        $response->assertSee($user->name);
    }

    public function test_comment_shows_current_user_as_author(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        Comment::factory()->create([
            'recipe_version_id' => $version->id,
            'user_id' => $user->id,
            'is_ai' => false,
            'content' => 'User comment',
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('Test User');
    }

    public function test_comment_validation_requires_content(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($user)->post(
            route('comments.store', [$recipe, $version]),
            ['content' => '']
        );

        $response->assertSessionHasErrors('content');
    }

    public function test_user_cannot_comment_on_another_users_recipe(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $recipe = Recipe::factory()->create(['user_id' => $owner->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($otherUser)->post(
            route('comments.store', [$recipe, $version]),
            ['content' => 'Test comment']
        );

        $response->assertStatus(403);
    }

    public function test_guest_cannot_add_comment(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->post(
            route('comments.store', [$recipe, $version]),
            ['content' => 'Test comment']
        );

        $response->assertRedirect(route('login'));
    }

    public function test_empty_comments_shows_encouraging_message(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $version = RecipeVersion::factory()->create([
            'recipe_id' => $recipe->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

        $response->assertStatus(200);
        $response->assertSee('No comments yet. Be the first to comment!');
    }
}
