<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;

class ClientsSeeder extends Seeder
{
    public function run()
    {
        $sales = User::where('user_type','sales_rep')->pluck('id')->toArray();
        foreach (range(1,30) as $i) {
            Client::create([
                'name' => "Client $i",
                'email' => "client{$i}@example.com",
                'phone' => "0500000{$i}",
                'assigned_to' => $sales[array_rand($sales)],
                'status' => 'New',
            ]);
        }
    }
}
