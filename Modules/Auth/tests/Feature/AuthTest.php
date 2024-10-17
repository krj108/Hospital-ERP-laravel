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
        // إعداد مستخدم للاختبار
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // محاكاة طلب تسجيل الدخول
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // التحقق من نجاح تسجيل الدخول
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
        // إعداد مستخدم للاختبار
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // محاكاة طلب تسجيل الدخول بكلمة مرور خاطئة
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // التحقق من فشل تسجيل الدخول
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid login credentials.',
        ]);
    }

    /** @test */
    public function user_can_logout_successfully()
    {
        // إعداد مستخدم للاختبار وتسجيل دخوله
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // محاكاة طلب تسجيل الخروج
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // التحقق من نجاح عملية تسجيل الخروج
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Logged out successfully',
        ]);
    }

    /** @test */
    public function user_can_update_profile()
    {
        // إعداد مستخدم للاختبار
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // محاكاة طلب تحديث الملف الشخصي
        $response = $this->putJson('/api/me', [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
            'current_password' => 'password123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // التحقق من نجاح التحديث
        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
        ]);
    }

    /** @test */
    public function user_cannot_update_profile_with_invalid_password()
    {
        // إعداد مستخدم للاختبار
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // محاكاة طلب تحديث الملف الشخصي بكلمة مرور غير صحيحة
        $response = $this->putJson('/api/me', [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
            'current_password' => 'wrongpassword',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // التحقق من فشل التحديث بسبب كلمة مرور غير صحيحة
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Invalid password.',
        ]);
    }

    /** @test */
    public function user_can_update_password()
    {
        // إعداد مستخدم للاختبار
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // محاكاة طلب تحديث كلمة المرور
        $response = $this->putJson('/api/me/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // التحقق من نجاح التحديث
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Password updated successfully.',
        ]);
    }

    /** @test */
    public function user_cannot_update_password_with_invalid_current_password()
    {
        // إعداد مستخدم للاختبار
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // محاكاة طلب تحديث كلمة المرور بكلمة مرور حالية غير صحيحة
        $response = $this->putJson('/api/me/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // التحقق من فشل التحديث بسبب كلمة مرور حالية غير صحيحة
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Current password is incorrect.',
        ]);
    }
}

