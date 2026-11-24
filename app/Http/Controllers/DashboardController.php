<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $awaitingApproval = MembershipTransaction::query()->where('status', 'paid');
        $awaitingVerification = Payment::query()->where('status', 'pending');
        $expiringMemberships = Member::query()->where('status', 'active')
            ->whereDate('expires_at', '>=', today())
            ->whereDate('expires_at', '<=', today()->addDays(30));

        return view('admin.dashboard', [
            'totalMembers' => Member::query()->count(),
            'activeMembers' => Member::query()->where('status', 'active')->count(),
            'pendingTransactions' => MembershipTransaction::query()->whereIn('status', ['pending', 'paid'])->count(),
            'paidTransactions' => (clone $awaitingApproval)->count(),
            'collectedPayments' => Payment::query()->where('status', 'paid')->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'approvalsToReview' => $awaitingApproval->with('member.user')->oldest()->orderBy('id')->limit(4)->get(),
            'pendingPaymentCount' => (clone $awaitingVerification)->count(),
            'paymentsToReview' => $awaitingVerification->with('transaction.member.user')->oldest()->orderBy('id')->limit(4)->get(),
            'recentTransactions' => MembershipTransaction::query()->with('member.user')->latest()->orderByDesc('id')->limit(6)->get(),
            'expiringMemberCount' => (clone $expiringMemberships)->count(),
            'expiringMembers' => $expiringMemberships->with(['user', 'category'])->orderBy('expires_at')->orderBy('id')->limit(5)->get(),
            'categoryOverview' => MembershipCategory::query()->withCount('members')->orderBy('name')->get(),
            'levelOverview' => MembershipLevel::query()->withCount('members')->orderBy('rank')->orderBy('name')->get(),
        ]);
    }
}
