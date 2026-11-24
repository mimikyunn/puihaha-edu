<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['pending', 'paid', 'cancelled'])],
        ]);
        $payments = Payment::query()->with('transaction.member.user')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('payment_number', 'like', '%'.$search.'%')->orWhere('reference_number', 'like', '%'.$search.'%')->orWhereHas('transaction', fn ($query) => $query->where('transaction_number', 'like', '%'.$search.'%'))->orWhereHas('transaction.member.user', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.payments', compact('payments'));
    }

    public function create(Request $request): View
    {
        $request->validate(['transaction' => ['nullable', 'integer', 'exists:membership_transactions,id']]);
        $transactions = MembershipTransaction::query()->with('member.user')->where('status', 'pending')->whereHas('member', fn ($query) => $query->where('status', '!=', 'inactive'))->latest()->get();

        return view('admin.payment-form', compact('transactions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'membership_transaction_id' => ['required', 'integer', 'exists:membership_transactions,id'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'card', 'e_wallet'])],
            'reference_number' => ['nullable', 'required_unless:payment_method,cash', 'string', 'max:100', 'unique:payments,reference_number'],
        ]);
        $payment = DB::transaction(function () use ($data): Payment {
            $transaction = MembershipTransaction::query()->findOrFail($data['membership_transaction_id']);
            $member = Member::query()->lockForUpdate()->findOrFail($transaction->member_id);
            $transaction = MembershipTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->status !== 'pending' || $member->status === 'inactive') {
                throw ValidationException::withMessages(['membership_transaction_id' => 'Select a pending transaction for an enabled member.']);
            }
            if ((int) round((float) $data['amount'] * 100) > $transaction->balanceCents()) {
                throw ValidationException::withMessages(['amount' => 'Payment cannot exceed the outstanding balance.']);
            }
            $payment = $transaction->payments()->create([...$data, 'status' => 'paid', 'paid_at' => now()]);
            if ($transaction->balanceCents() === 0) {
                $transaction->update(['status' => 'paid']);
            }

            return $payment;
        });

        return to_route('payments.show', $payment)->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment): View
    {
        return view('admin.payment-show', ['payment' => $payment->load('transaction.member.user')]);
    }
}
