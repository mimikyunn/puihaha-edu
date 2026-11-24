<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'member_number', 'membership_category_id', 'membership_level_id', 'phone_number', 'address', 'latitude', 'longitude', 'joined_at', 'expires_at', 'status'];

    protected static function booted(): void
    {
        static::creating(function (Member $member): void {
            $member->member_number ??= 'MEM-'.Str::upper((string) Str::ulid());
        });
    }

    protected function casts(): array
    {
        return ['joined_at' => 'date', 'expires_at' => 'date', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }
}
