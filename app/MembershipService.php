<?php

namespace App;

use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MembershipService
{
    public function createTransaction(Member $member, string $type, ?string $notes = null): MembershipTransaction
    {
        return DB::transaction(function () use ($member, $type, $notes): MembershipTransaction {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);
            $hasApplication = $member->transactions()->where('type', 'application')->whereIn('status', ['approved', 'expired'])->exists();
            if ($member->status === 'inactive') {
                throw ValidationException::withMessages(['member_id' => 'Reactivate this member before creating a transaction.']);
            }
            if ($member->transactions()->whereIn('status', ['pending', 'paid'])->exists()) {
                throw ValidationException::withMessages(['member_id' => 'This member already has a pending or paid transaction.']);
            }
            if (($type === 'renewal') !== $hasApplication) {
                throw ValidationException::withMessages(['type' => $hasApplication ? 'Use renewal for an existing membership.' : 'Approve an application before creating a renewal.']);
            }
            if (! $member->category?->is_active || ! $member->level?->is_active) {
                throw ValidationException::withMessages(['member_id' => 'Choose an active category and level for this member.']);
            }
            $amountCents = (int) round((float) $member->category->fee * 100) + (int) round((float) $member->level->fee * 100);

            $transaction = $member->transactions()->create([
                'membership_category_id' => $member->membership_category_id,
                'membership_level_id' => $member->membership_level_id,
                'type' => $type,
                'amount_due' => $amountCents / 100,
                'duration_months' => $member->level->duration_months,
                'status' => $amountCents === 0 ? 'paid' : 'pending',
                'notes' => $notes,
            ]);
            $member->user->notify(new MembershipApplicationSubmitted($transaction));

            return $transaction;
        });
    }

    public function approve(MembershipTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $member = Member::query()->lockForUpdate()->findOrFail($transaction->member_id);
            $transaction = MembershipTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->status !== 'paid' || $transaction->balanceCents() !== 0 || $member->status === 'inactive') {
                throw ValidationException::withMessages(['status' => 'Only a fully paid transaction for an enabled member can be approved.']);
            }
            $startsAt = $transaction->type === 'renewal' && $member->expires_at?->gte(today())
                ? $member->expires_at->copy()->addDay() : today();
            $endsAt = $startsAt->copy()->addMonthsNoOverflow($transaction->duration_months)->subDay();
            $transaction->update(['status' => 'approved', 'approved_at' => now(), 'starts_at' => $startsAt, 'ends_at' => $endsAt]);
            $member->update([
                'membership_category_id' => $transaction->membership_category_id,
                'membership_level_id' => $transaction->membership_level_id ?? $member->membership_level_id,
                'status' => 'active',
                'joined_at' => $member->joined_at ?? $startsAt,
                'expires_at' => $endsAt,
            ]);
        });
    }

    public function expireMemberships(): void
    {
        MembershipTransaction::query()->where('status', 'approved')->whereDate('ends_at', '<', today())->update(['status' => 'expired']);
        Member::query()->where('status', 'active')->whereDate('expires_at', '<', today())->update(['status' => 'expired']);
    }
}
