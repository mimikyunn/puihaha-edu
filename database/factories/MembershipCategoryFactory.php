<?php

namespace Database\Factories;

use App\Models\MembershipCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MembershipCategory> */
class MembershipCategoryFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'fee' => 0,
            'is_active' => true,
        ];
    }
}
