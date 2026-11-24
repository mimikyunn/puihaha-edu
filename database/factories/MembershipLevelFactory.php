<?php

namespace Database\Factories;

use App\Models\MembershipLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MembershipLevel> */
class MembershipLevelFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'fee' => 500,
            'duration_months' => 12,
            'benefits' => "Member access\nCommunity updates",
            'rank' => 0,
            'is_active' => true,
        ];
    }
}
