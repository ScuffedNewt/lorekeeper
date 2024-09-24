<?php

namespace Database\Factories\Rank;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rank\Rank;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rank\Rank>
 */
class RankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'parsed_description' => $this->faker->sentence,
        ];
    }
}
