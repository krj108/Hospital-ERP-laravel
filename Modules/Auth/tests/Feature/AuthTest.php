<?php

namespace Modules\Auth\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Simulate a login request
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert that the login is successful with a 200 response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'roles',
            'name',
        ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Simulate a login request with an incorrect password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert that the login fails with a 401 status code
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid login credentials.',
        ]);
    }

    /** @test */
    public function user_can_logout_successfully()
    {
        // Create a user for testing and log them in
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Simulate a logout request
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert that the logout was successful with a 200 status code
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Logged out successfully',
        ]);
    }

    /** @test */
    public function user_can_update_profile()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Simulate a profile update request
        $response = $this->putJson('/api/me', [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
            'current_password' => 'password123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert that the profile update is successful
        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
        ]);
    }

    /** @test */
    public function user_cannot_update_profile_with_invalid_password()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Simulate a profile update request with an invalid password
        $response = $this->putJson('/api/me', [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
            'current_password' => 'wrongpassword',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert that the profile update fails due to invalid password
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Invalid password.',
        ]);
    }

    /** @test */
    public function user_can_update_password()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Simulate a password update request
        $response = $this->putJson('/api/me/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert that the password update is successful
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Password updated successfully.',
        ]);
    }

    /** @test */
    public function user_cannot_update_password_with_invalid_current_password()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Simulate a password update request with an invalid current password
        $response = $this->putJson('/api/me/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert that the password update fails due to invalid current password
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Current password is incorrect.',
        ]);
    }
}
