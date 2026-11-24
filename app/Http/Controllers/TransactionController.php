<?php

namespace App\Http\Controllers;

use App\MembershipService;
use App\Models\Member;
use App\Models\MembershipTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', Rule::in(['pending', 'paid', 'approved', 'expired', 'cancelled'])]]);
        $transactions = MembershipTransaction::query()->with(['member.user', 'category', 'level'])->withSum(['payments as paid_total' => fn ($query) => $query->where('status', 'paid')], 'amount')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('transaction_number', 'like', '%'.$search.'%')->orWhereHas('member.user', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.transactions', compact('transactions'));
    }

    public function create(Request $request): View
    {
        $request->validate([
            'member' => ['nullable', 'integer', 'exists:members,id'],
            'type' => ['nullable', Rule::in(['application', 'renewal'])],
        ]);

        $members = Member::query()->with(['user', 'category', 'level'])
            ->withExists(['transactions as has_approved_application' => fn ($query) => $query->where('type', 'application')->whereIn('status', ['approved', 'expired'])])
            ->where('status', '!=', 'inactive')
            ->whereDoesntHave('transactions', fn ($query) => $query->whereIn('status', ['pending', 'paid']))
            ->when($request->input('type') === 'renewal', fn ($query) => $query->whereHas('transactions', fn ($query) => $query->where('type', 'application')->whereIn('status', ['approved', 'expired'])))
            ->orderByDesc('id')->get();
        $defaultType = $request->input('type') === 'renewal' || $members->firstWhere('id', $request->input('member'))?->has_approved_application ? 'renewal' : 'application';

        return view('admin.transaction-form', compact('members', 'defaultType'));
    }

    public function store(Request $request, MembershipService $service): RedirectResponse
    {
        $data = $request->validate(['member_id' => ['required', 'integer', 'exists:members,id'], 'type' => ['required', Rule::in(['application', 'renewal'])], 'notes' => ['nullable', 'string', 'max:2000']]);
        $transaction = $service->createTransaction(Member::query()->findOrFail($data['member_id']), $data['type'], $data['notes'] ?? null);

        return to_route('transactions.show', $transaction)->with('success', 'Transaction created. The fee is calculated from the category and level.');
    }

    public function show(MembershipTransaction $transaction): View
    {
        return view('admin.transaction-show', ['transaction' => $transaction->load(['member.user', 'category', 'level', 'payments'])]);
    }

    public function approve(MembershipTransaction $transaction, MembershipService $service): RedirectResponse
    {
        $service->approve($transaction);

        return back()->with('success', 'Transaction approved. Membership validity has been updated.');
    }

    public function cancel(MembershipTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction): void {
            Member::query()->lockForUpdate()->findOrFail($transaction->member_id);
            $transaction = MembershipTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if (! in_array($transaction->status, ['pending', 'paid'], true) || $transaction->paidCents() > 0) {
                throw ValidationException::withMessages(['status' => 'Only an open transaction with no recorded payment can be cancelled.']);
            }
            $transaction->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Transaction cancelled.');
    }
}
