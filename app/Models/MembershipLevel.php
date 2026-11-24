<?php

namespace App\Models;

use Database\Factories\MembershipLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipLevel extends Model
{
    /** @use HasFactory<MembershipLevelFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'rank', 'is_active', 'fee', 'duration_months', 'benefits'];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }
}
