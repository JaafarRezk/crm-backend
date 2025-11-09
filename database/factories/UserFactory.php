<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition()
    {
        $types = ['admin', 'manager', 'sales_rep'];

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password', 
            'phone' => $this->faker->phoneNumber(),
            'user_type' => $this->faker->randomElement($types),
            'last_login' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ];
    }

    /** state helpers */
    public function admin()
    {
        return $this->state(fn(array $attributes) => ['user_type' => 'admin']);
    }

    public function manager()
    {
        return $this->state(fn(array $attributes) => ['user_type' => 'manager']);
    }

    public function salesRep()
    {
        return $this->state(fn(array $attributes) => ['user_type' => 'sales_rep']);
    }
}
