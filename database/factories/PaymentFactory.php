<?php

namespace Database\Factories;

use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'membership_transaction_id' => MembershipTransaction::factory(),
            'amount' => 500,
            'payment_method' => 'cash',
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }
}
