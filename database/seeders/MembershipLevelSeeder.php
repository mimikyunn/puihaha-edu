<?php

namespace Database\Seeders;

use App\Models\MembershipLevel;
use Illuminate\Database\Seeder;

class MembershipLevelSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Basic' => 500, 'Silver' => 1000, 'Gold' => 2000, 'Platinum' => 3500] as $name => $fee) {
            MembershipLevel::query()->firstOrCreate(['name' => $name], ['description' => $name.' membership plan.', 'fee' => $fee, 'duration_months' => 12, 'benefits' => "Member access\nCommunity updates", 'rank' => array_search($name, ['Basic', 'Silver', 'Gold', 'Platinum']), 'is_active' => true]);
        }
    }
}
