<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Client;
use App\Models\Communication;
use Carbon\Carbon;

class ClientCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_client_and_communication_updates_client_last_communication()
    {
        $user = User::factory()->create(['user_type' => 'sales_rep']);
        $this->actingAs($user, 'api');

        $clientResp = $this->postJson('/api/clients', [
            'name' => 'C1',
            'email' => 'c1@example.com',
            'assigned_to' => $user->id,
        ]);

        // create usually returns 201
        $clientResp->assertStatus(201);

        $clientId = $clientResp->json('data.id') ?? $clientResp->json('data')['id'];

        $this->postJson("/api/clients/{$clientId}/communications", [
            'type' => 'call',
            'notes' => 'test'
        ])->assertStatus(201);

        $client = Client::find($clientId);
        $this->assertNotNull($client->last_communication_at);
    }
}
