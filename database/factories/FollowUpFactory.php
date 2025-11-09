<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FollowUpFactory extends Factory
{
    protected $model = \App\Models\FollowUp::class;

    public function definition()
    {
        return [
            'client_id' => null,
            'assigned_to' => null,
            'scheduled_for' => $this->faker->dateTimeBetween('now', '+2 months'),
            'status' => $this->faker->randomElement(['pending', 'done', 'cancelled']),
            'notes' => $this->faker->optional()->paragraph,
            'created_by' => null,
        ];
    }
}
