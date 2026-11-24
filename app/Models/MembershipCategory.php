<?php

namespace App\Models;

use Database\Factories\MembershipCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipCategory extends Model
{
    /** @use HasFactory<MembershipCategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'fee', 'duration_months', 'is_active'];

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
