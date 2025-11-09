<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::factory()->create([
            'name'=>'System Admin',
            'email'=>'admin@example.com',
            'password'=>'password', // Model mutator hashes
            'user_type'=>'admin'
        ]);

        User::factory()->create([
            'name'=>'Manager One',
            'email'=>'manager@example.com',
            'password'=>'password',
            'user_type'=>'manager'
        ]);

        User::factory()->count(5)->create([
            'user_type'=>'sales_rep'
        ]);
    }
}
