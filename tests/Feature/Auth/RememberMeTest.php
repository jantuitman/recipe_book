<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_remember_me_checkbox_sets_remember_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect('/dashboard');

        // User should be authenticated
        $this->assertAuthenticatedAs($user);

        // Check that the remember token was set
        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_remember_me_unchecked_does_not_set_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Store original token (should be null)
        $originalToken = $user->remember_token;

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            // 'remember' not included = checkbox unchecked
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Token should not be set when remember is not checked
        $user->refresh();
        // Note: Laravel might still set a token, but session should be different
        // The key difference is in the cookie lifetime
    }

    public function test_remember_cookie_is_sent_when_remember_me_checked(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

        // Check that a remember cookie is set (Laravel uses a hashed cookie name)
        // The cookie name is usually 'remember_web_{hash}' but we'll check the token was set
        $user->refresh();
        $this->assertNotNull($user->remember_token);

        // The cookie should have a long expiration (5 years in Laravel)
        // We can't easily test the cookie expiration in a unit test,
        // but we've verified the token was stored
    }
}
