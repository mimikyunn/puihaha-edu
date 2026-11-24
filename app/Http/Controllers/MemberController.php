<?php

namespace App\Http\Controllers;

use App\MembershipService;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255'], 'category' => ['nullable', 'integer'], 'level' => ['nullable', 'integer'], 'status' => ['nullable', Rule::in(['pending', 'active', 'expired', 'inactive'])]]);
        $members = Member::query()->with(['user', 'category', 'level'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
                $query->where('member_number', 'like', '%'.$search.'%')->orWhereHas('user', fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
            }))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('membership_category_id', $category))
            ->when($filters['level'] ?? null, fn ($query, $level) => $query->where('membership_level_id', $level))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.members', ['members' => $members, 'categories' => MembershipCategory::query()->orderBy('name')->get(), 'levels' => MembershipLevel::query()->orderBy('rank')->get()]);
    }

    public function create(): View
    {
        return $this->form(new Member);
    }

    public function edit(Member $member): View
    {
        return $this->form($member->load('user'));
    }

    private function form(Member $member): View
    {
        return view('admin.member-form', ['member' => $member, 'categories' => MembershipCategory::query()->where('is_active', true)->orWhere('id', $member->membership_category_id)->orderBy('name')->get(), 'levels' => MembershipLevel::query()->where('is_active', true)->orWhere('id', $member->membership_level_id)->orderBy('rank')->get()]);
    }

    public function store(Request $request, MembershipService $service): RedirectResponse
    {
        $data = $this->validated($request);
        $member = DB::transaction(function () use ($data, $service): Member {
            $user = User::query()->create(['name' => $data['name'], 'email' => $data['email'], 'password' => Str::random(64), 'role' => 'member']);
            $member = Member::query()->create([...$data, 'user_id' => $user->id]);
            $service->createTransaction($member, 'application');

            return $member;
        });

        return to_route('members.show', $member)->with('success', 'Member registered. An application transaction is ready for payment.');
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $data = $this->validated($request, $member);
        DB::transaction(function () use ($member, $data): void {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);
            if ($member->transactions()->whereIn('status', ['pending', 'paid'])->exists() && ($data['membership_category_id'] != $member->membership_category_id || $data['membership_level_id'] != $member->membership_level_id)) {
                throw ValidationException::withMessages(['membership_level_id' => 'Complete or cancel the open transaction before changing category or level.']);
            }
            $member->user->update(['name' => $data['name'], 'email' => $data['email']]);
            $member->update($data);
        });

        return to_route('members.show', $member)->with('success', 'Member details updated.');
    }

    /** @return array{name: string, email: string, membership_category_id: mixed, membership_level_id: mixed, phone_number?: ?string, member_number?: ?string, address?: ?string, latitude?: mixed, longitude?: mixed} */
    private function validated(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member?->user_id)],
            'member_number' => [$member ? 'required' : 'nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('members', 'member_number')->ignore($member?->id)],
            'membership_category_id' => ['required', Rule::exists('membership_categories', 'id')->where(fn ($query) => $query->where('is_active', true)->when($member, fn ($query) => $query->orWhere('id', $member->membership_category_id)))],
            'membership_level_id' => ['required', Rule::exists('membership_levels', 'id')->where(fn ($query) => $query->where('is_active', true)->when($member, fn ($query) => $query->orWhere('id', $member->membership_level_id)))],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]);
    }

    public function show(Member $member): View
    {
        return view('admin.member-show', [
            'member' => $member->load(['user', 'category', 'level']),
            'transactions' => $member->transactions()->with(['category', 'level', 'payments'])->latest()->paginate(10),
            'openTransaction' => $member->transactions()->whereIn('status', ['pending', 'paid'])->first(),
        ]);
    }

    public function destroy(Member $member): RedirectResponse
    {
        DB::transaction(function () use ($member): void {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);
            $member->update(['status' => 'inactive']);
        });

        return back()->with('success', 'Member deactivated. Transaction and payment history has been kept.');
    }

    public function reactivate(Member $member): RedirectResponse
    {
        DB::transaction(function () use ($member): void {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);
            abort_unless($member->status === 'inactive', 422);
            $member->update(['status' => $member->expires_at ? ($member->expires_at->gte(today()) ? 'active' : 'expired') : 'pending']);
        });

        return back()->with('success', 'Member reactivated.');
    }

    public function record(Member $member): View
    {
        return view('admin.member-record', ['member' => $member->load(['user', 'category', 'level'])]);
    }
}
