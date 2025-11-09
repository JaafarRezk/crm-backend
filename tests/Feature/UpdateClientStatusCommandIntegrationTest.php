<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\Communication;
use Carbon\Carbon;

class UpdateClientStatusCommandIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_command_updates_clients_in_database_when_run()
    {
        // Prepare clients and communications
        $c1 = Client::factory()->create(['status' => 'New', 'last_communication_at' => null]);
        Communication::factory()->count(3)->create([
            'client_id' => $c1->id,
            'date' => Carbon::now()->subDays(2),
        ]);

        $c2 = Client::factory()->create(['status' => 'Active', 'last_communication_at' => Carbon::now()->subDays(40)]);

        // Run the command
        $this->artisan('crm:update-client-statuses')->assertExitCode(0);

        // Verify in DB
        $this->assertDatabaseHas('clients', ['id' => $c1->id, 'status' => 'Hot']);
        $this->assertDatabaseHas('clients', ['id' => $c2->id, 'status' => 'Inactive']);
    }
}

