<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Client;

class FollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_followup_sends_notification()
    {
        Notification::fake();

        $user = User::factory()->create(['user_type'=>'sales_rep']);
        $assignee = User::factory()->create(['user_type'=>'sales_rep']);
        $this->actingAs($user,'api');

        $client = Client::factory()->create(['assigned_to'=>$assignee->id]);

        $payload = [
            'client_id' => $client->id,
            'assigned_to' => $assignee->id,
            'due_date' => now()->addDay()->toDateString()
        ];

        $this->postJson('/api/follow-ups',$payload)->assertStatus(201);

        Notification::assertSentTo($assignee, \App\Notifications\FollowUpAssignedNotification::class);
    }
}


