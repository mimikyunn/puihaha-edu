<?php

namespace App\Console\Commands;

use App\MembershipService;
use Illuminate\Console\Command;

class ExpireMemberships extends Command
{
    protected $signature = 'membership:expire';

    protected $description = 'Mark elapsed memberships and approved transactions as expired';

    public function handle(MembershipService $service): int
    {
        $service->expireMemberships();
        $this->info('Membership expiry statuses updated.');

        return self::SUCCESS;
    }
}
