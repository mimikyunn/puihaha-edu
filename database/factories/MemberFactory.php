<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Member> */
class MemberFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'membership_category_id' => MembershipCategory::factory(),
            'membership_level_id' => MembershipLevel::factory(),
            'phone_number' => fake()->phoneNumber(),
            'status' => 'pending',
        ];
    }
}
