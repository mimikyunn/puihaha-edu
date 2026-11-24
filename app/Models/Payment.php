<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = ['membership_transaction_id', 'payment_number', 'amount', 'payment_method', 'reference_number', 'status', 'paid_at'];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $payment->payment_number ??= 'PAY-'.Str::upper((string) Str::ulid());
        });
    }

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(MembershipTransaction::class, 'membership_transaction_id');
    }
}
