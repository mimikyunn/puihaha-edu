<?php

namespace App\Models;

use Database\Factories\MembershipTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MembershipTransaction extends Model
{
    /** @use HasFactory<MembershipTransactionFactory> */
    use HasFactory;

    protected $fillable = ['transaction_number', 'member_id', 'membership_category_id', 'membership_level_id', 'type', 'amount_due', 'duration_months', 'starts_at', 'ends_at', 'status', 'approved_at', 'notes'];

    protected static function booted(): void
    {
        static::creating(function (MembershipTransaction $transaction): void {
            $transaction->transaction_number ??= 'TXN-'.Str::upper((string) Str::ulid());
        });
    }

    protected function casts(): array
    {
        return ['amount_due' => 'decimal:2', 'starts_at' => 'date', 'ends_at' => 'date', 'approved_at' => 'datetime'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidCents(): int
    {
        return (int) round((float) $this->payments()->where('status', 'paid')->sum('amount') * 100);
    }

    public function balanceCents(): int
    {
        return max(0, (int) round((float) $this->amount_due * 100) - $this->paidCents());
    }
}
