<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FollowUp;
use App\Models\Client;
use App\Models\User;

class FollowUpsSeeder extends Seeder
{
    public function run()
    {
        $sales = User::where('user_type','sales_rep')->get();
        $clients = Client::all();

        foreach ($clients as $client) {
            if (rand(0,1)) {
                FollowUp::create([
                    'client_id' => $client->id,
                    'assigned_to' => $sales->random()->id,
                    'created_by' => $sales->random()->id,
                    'due_date' => now()->addDays(rand(-2,5))->toDateString(),
                    'status' => 'pending',
                    'notes' => 'Seed follow up',
                ]);
            }
        }
    }
}
