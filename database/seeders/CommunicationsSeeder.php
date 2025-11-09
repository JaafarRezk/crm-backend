<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Communication;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;

class CommunicationsSeeder extends Seeder
{
    public function run()
    {
        $clients = Client::all();
        $users = User::where('user_type','sales_rep')->get();

        foreach ($clients as $client) {
            $count = rand(0,5);
            for ($i=0;$i<$count;$i++) {
                Communication::create([
                    'client_id' => $client->id,
                    'created_by' => $users->random()->id,
                    'type' => ['call','email','meeting'][array_rand(['call','email','meeting'])],
                    'date' => Carbon::now()->subDays(rand(0,40)),
                    'notes' => 'Seeded communication',
                ]);
            }
        }
    }
}
