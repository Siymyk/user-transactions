<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_transfer_money_to_another_user()
    {
        $from = User::factory()->create(['balance' => 200]);
        $to = User::factory()->create();

        $response = $this->postJson('/api/transactions/transfer', [
            'fromUserId' => $from->id,
            'toUserId' => $to->id,
            'amount' => 100
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Перевод выполнен']);

        $this->assertEquals(100, $from->fresh()->balance);
        $this->assertEquals(100, $to->fresh()->balance);
    }

    public function test_user_can_view_transaction_history()
    {
        $user = User::factory()->create(['balance' => 500]);
        $receiver = User::factory()->create();

        $this->postJson('/api/transactions/transfer', [
            'fromUserId' => $user->id,
            'toUserId' => $receiver->id,
            'amount' => 200
        ]);

        $response = $this->getJson("/api/users/{$user->id}/transactions");
        $response->assertStatus(200)
            ->assertJsonStructure([['id', 'from_user_id', 'to_user_id', 'amount', 'created_at']]);
    }
}
