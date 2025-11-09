<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Communication;
use App\Models\Client;
use App\Models\User;

class CommunicationFactory extends Factory
{
    protected $model = Communication::class;

    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'type' => $this->faker->randomElement(['call', 'email', 'meeting']),
            'date' => $this->faker->dateTimeThisYear(),
            'notes' => $this->faker->optional()->paragraph,
            'created_by' => User::factory(),
        ];
    }
}
