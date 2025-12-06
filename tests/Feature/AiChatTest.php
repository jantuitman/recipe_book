<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ChatMessage;
use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the OpenAI service for all tests
        $this->mockOpenAiService = Mockery::mock(OpenAiService::class);
        $this->app->instance(OpenAiService::class, $this->mockOpenAiService);
    }

    /** @test */
    public function chat_page_requires_authentication()
    {
        $response = $this->get('/chat');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_chat_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/chat');
        $response->assertOk();
        $response->assertViewIs('chat.index');
    }

    /** @test */
    public function authenticated_user_can_get_chat_messages()
    {
        $user = User::factory()->create();

        // Create some chat messages for the user
        ChatMessage::factory()->count(5)->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->get('/chat/messages');

        $response->assertOk();
        $response->assertJson([
            'success' => true
        ]);

        $data = $response->json();
        $this->assertCount(5, $data['messages']);
    }

    /** @test */
    public function user_only_sees_their_own_messages()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create messages for both users
        ChatMessage::factory()->count(3)->create(['user_id' => $user1->id]);
        ChatMessage::factory()->count(2)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get('/chat/messages');

        $data = $response->json();
        $this->assertCount(3, $data['messages']);
    }

    /** @test */
    public function messages_are_in_chronological_order()
    {
        $user = User::factory()->create();

        // Create messages with different timestamps
        $msg1 = ChatMessage::factory()->create([
            'user_id' => $user->id,
            'content' => 'First message',
            'created_at' => now()->subHours(2)
        ]);

        $msg2 = ChatMessage::factory()->create([
            'user_id' => $user->id,
            'content' => 'Second message',
            'created_at' => now()->subHour()
        ]);

        $msg3 = ChatMessage::factory()->create([
            'user_id' => $user->id,
            'content' => 'Third message',
            'created_at' => now()
        ]);

        $response = $this->actingAs($user)->get('/chat/messages');

        $messages = $response->json('messages');
        $this->assertEquals('First message', $messages[0]['content']);
        $this->assertEquals('Second message', $messages[1]['content']);
        $this->assertEquals('Third message', $messages[2]['content']);
    }

    /** @test */
    public function user_can_send_message_and_receive_response()
    {
        $user = User::factory()->create();

        // Mock OpenAI service response (now returns string)
        $this->mockOpenAiService
            ->shouldReceive('chatResponse')
            ->once()
            ->with('How do I make pasta?', Mockery::any(), Mockery::any())
            ->andReturn('Here is how to make pasta...');

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'How do I make pasta?'
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'response' => 'Here is how to make pasta...',
            'has_recipe' => false
        ]);

        // Verify both messages were saved
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'How do I make pasta?'
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => 'Here is how to make pasta...'
        ]);
    }

    /** @test */
    public function chat_validates_message_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => ''
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    /** @test */
    public function chat_validates_message_maximum_length()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => str_repeat('a', 2001)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    /** @test */
    public function chat_maintains_conversation_context()
    {
        $user = User::factory()->create();

        // Create initial conversation
        ChatMessage::factory()->create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'What is the best way to cook steak?'
        ]);

        ChatMessage::factory()->create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => 'The best way to cook steak is...'
        ]);

        // Mock OpenAI service and verify it receives context
        $this->mockOpenAiService
            ->shouldReceive('chatResponse')
            ->once()
            ->with('Can you elaborate?', Mockery::on(function ($history) {
                // Verify history includes previous context
                return count($history) >= 2
                    && $history[0]['content'] === 'What is the best way to cook steak?'
                    && $history[1]['content'] === 'The best way to cook steak is...';
            }), Mockery::any())
            ->andReturn('Sure, I can elaborate on that...');

        // Send follow-up message
        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'Can you elaborate?'
        ]);

        $response->assertOk();
    }

    /** @test */
    public function chat_handles_ai_service_errors_gracefully()
    {
        $user = User::factory()->create();

        // Mock OpenAI service to throw exception
        $this->mockOpenAiService
            ->shouldReceive('chatResponse')
            ->once()
            ->with('Test message', Mockery::any(), Mockery::any())
            ->andThrow(new \Exception('API error'));

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'Test message'
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false
        ]);

        // User message should still be saved
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'Test message'
        ]);
    }

    /** @test */
    public function guest_cannot_send_chat_message()
    {
        $response = $this->postJson('/chat', [
            'message' => 'Hello'
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function guest_cannot_get_chat_messages()
    {
        $response = $this->get('/chat/messages');
        $response->assertRedirect('/login');
    }
}
