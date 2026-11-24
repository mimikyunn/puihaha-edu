<?php

namespace Database\Seeders;

use App\MembershipService;
use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        Member::factory()->count(5)->create()->each(function (Member $member): void {
            app(MembershipService::class)->createTransaction($member, 'application');
        });
    }
}
