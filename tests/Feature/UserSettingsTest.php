<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function settings_page_requires_authentication()
    {
        $response = $this->get('/settings');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_view_settings_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertViewIs('settings.edit');
        $response->assertViewHas('user', $user);
    }

    /** @test */
    public function settings_page_displays_current_preferences()
    {
        $user = User::factory()->create([
            'volume_unit' => 'cups',
            'weight_unit' => 'oz',
            'time_format' => 'hr_min',
        ]);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('selected', false); // Check that some option is selected
        // The view will have the correct values pre-selected
    }

    /** @test */
    public function user_can_update_volume_unit_preference()
    {
        $user = User::factory()->create([
            'volume_unit' => 'ml',
        ]);

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'cups',
            'weight_unit' => 'g',
            'time_format' => 'min',
        ]);

        $response->assertRedirect('/settings');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'volume_unit' => 'cups',
        ]);
    }

    /** @test */
    public function user_can_update_weight_unit_preference()
    {
        $user = User::factory()->create([
            'weight_unit' => 'g',
        ]);

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'ml',
            'weight_unit' => 'oz',
            'time_format' => 'min',
        ]);

        $response->assertRedirect('/settings');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'weight_unit' => 'oz',
        ]);
    }

    /** @test */
    public function user_can_update_time_format_preference()
    {
        $user = User::factory()->create([
            'time_format' => 'min',
        ]);

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'ml',
            'weight_unit' => 'g',
            'time_format' => 'hr_min',
        ]);

        $response->assertRedirect('/settings');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'time_format' => 'hr_min',
        ]);
    }

    /** @test */
    public function settings_update_shows_success_message()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'cups',
            'weight_unit' => 'oz',
            'time_format' => 'hr_min',
        ]);

        $response->assertRedirect('/settings');
        $response->assertSessionHas('success', 'Settings saved successfully!');
    }

    /** @test */
    public function settings_validation_requires_volume_unit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings', [
            'weight_unit' => 'g',
            'time_format' => 'min',
        ]);

        $response->assertSessionHasErrors('volume_unit');
    }

    /** @test */
    public function settings_validation_requires_valid_volume_unit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'invalid',
            'weight_unit' => 'g',
            'time_format' => 'min',
        ]);

        $response->assertSessionHasErrors('volume_unit');
    }

    /** @test */
    public function settings_validation_requires_weight_unit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'ml',
            'time_format' => 'min',
        ]);

        $response->assertSessionHasErrors('weight_unit');
    }

    /** @test */
    public function settings_validation_requires_valid_weight_unit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'ml',
            'weight_unit' => 'invalid',
            'time_format' => 'min',
        ]);

        $response->assertSessionHasErrors('weight_unit');
    }

    /** @test */
    public function settings_validation_requires_time_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'ml',
            'weight_unit' => 'g',
        ]);

        $response->assertSessionHasErrors('time_format');
    }

    /** @test */
    public function settings_validation_requires_valid_time_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings', [
            'volume_unit' => 'ml',
            'weight_unit' => 'g',
            'time_format' => 'invalid',
        ]);

        $response->assertSessionHasErrors('time_format');
    }

    /** @test */
    public function guest_cannot_update_settings()
    {
        $response = $this->patch('/settings', [
            'volume_unit' => 'cups',
            'weight_unit' => 'oz',
            'time_format' => 'hr_min',
        ]);

        $response->assertRedirect('/login');
    }
}
