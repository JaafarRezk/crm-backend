<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\Communication;
use App\Models\User;
use App\Console\Commands\UpdateClientStatusCommand;
use PHPUnit\Framework\Attributes\Test;

class UpdateClientStatusCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_marks_clients_hot_and_inactive()
    {
        $user = User::factory()->create();

        // Client with 3 communications in last 7 days → should become Hot
        $hotClient = Client::factory()->create(['status' => 'New', 'assigned_to' => $user->id]);
        Communication::factory()->count(3)->create([
            'client_id' => $hotClient->id,
            'created_by' => $user->id,
            'date' => now()->subDays(2)
        ]);

        // Client with no communication in last 30 days → should become Inactive
        $inactiveClient = Client::factory()->create(['status' => 'Active', 'assigned_to' => $user->id, 'last_communication_at' => now()->subDays(40)]);

        // Run the artisan command
        $this->artisan('crm:update-client-statuses')->assertExitCode(0);

        $this->assertEquals('Hot', $hotClient->fresh()->status);
        $this->assertEquals('Inactive', $inactiveClient->fresh()->status);
    }
}


