<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use Illuminate\Database\Seeder;

class MembershipCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Student', 'Regular', 'Senior Citizen', 'Corporate'] as $name) {
            MembershipCategory::query()->firstOrCreate(['name' => $name], ['description' => $name.' membership category.', 'fee' => 0, 'is_active' => true]);
        }
    }
}
