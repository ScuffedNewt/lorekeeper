<?php

namespace Database\Factories\User;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\User>
 */
class UserFactory extends Factory {
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'name'              => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password'          => Hash::make('password'), // Default password
            'remember_token'    => Str::random(10),
            'rank_id'           => 1,
        ];
    }
}
