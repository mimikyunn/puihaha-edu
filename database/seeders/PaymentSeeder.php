<?php

namespace Database\Seeders;

use App\Models\MembershipTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        MembershipTransaction::query()->where('status', 'pending')->whereDoesntHave('payments')->get()->each(function (MembershipTransaction $transaction): void {
            DB::transaction(function () use ($transaction): void {
                $transaction = MembershipTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
                $transaction->payments()->create(['amount' => $transaction->amount_due, 'payment_method' => 'cash', 'status' => 'paid', 'paid_at' => now()]);
                $transaction->update(['status' => 'paid']);
            });
        });
    }
}
