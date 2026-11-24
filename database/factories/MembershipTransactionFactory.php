<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MembershipTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MembershipTransaction> */
class MembershipTransactionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'membership_category_id' => fn (array $attributes) => Member::query()->findOrFail($attributes['member_id'])->membership_category_id,
            'membership_level_id' => fn (array $attributes) => Member::query()->findOrFail($attributes['member_id'])->membership_level_id,
            'amount_due' => 500,
            'duration_months' => 12,
            'type' => 'application',
            'status' => 'pending',
        ];
    }
}
