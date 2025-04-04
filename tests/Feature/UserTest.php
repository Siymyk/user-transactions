<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Test',
            'email' => 'test996@gmail.com'
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Пользователь создан']);
    }

    public function test_user_can_deposit_money()
    {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/deposit", [
            'amount' => 300
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Баланс пополнен']);
    }
}
