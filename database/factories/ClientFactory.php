<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;
use App\Models\User;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['New','Active','Hot','Inactive']),
            'assigned_to' => User::factory(), // Assign a user by default
            'last_communication_at' => null,
        ];
    }

    public function withSalesRep($userId)
    {
        return $this->state(fn() => ['assigned_to' => $userId]);
    }
}
