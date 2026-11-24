<?php

namespace Database\Seeders;

use App\MembershipService;
use App\Models\Member;
use Illuminate\Database\Seeder;

class MembershipTransactionSeeder extends Seeder
{
    public function run(): void
    {
        Member::query()->where('status', '!=', 'inactive')->whereDoesntHave('transactions')->get()->each(function (Member $member): void {
            app(MembershipService::class)->createTransaction($member, 'application');
        });
    }
}
