<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    public function index(): View
    {
        return view('admin.levels', ['levels' => MembershipLevel::query()->withCount('members')->orderBy('rank')->paginate(12)]);
    }

    public function create(): View
    {
        return view('admin.level-form', ['level' => new MembershipLevel(['is_active' => true, 'fee' => 0, 'duration_months' => 12, 'rank' => 0])]);
    }

    public function edit(MembershipLevel $level): View
    {
        return view('admin.level-form', compact('level'));
    }

    public function store(Request $request): RedirectResponse
    {
        MembershipLevel::query()->create($this->validated($request));

        return to_route('member-levels')->with('success', 'Level created.');
    }

    public function update(Request $request, MembershipLevel $level): RedirectResponse
    {
        $level->update($this->validated($request, $level));

        return to_route('member-levels')->with('success', 'Level updated. Existing transaction charges stay the same.');
    }

    /** @return array{name: string, description?: ?string, fee: mixed, duration_months: mixed, benefits?: ?string, rank: mixed, is_active: bool} */
    private function validated(Request $request, ?MembershipLevel $level = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_levels', 'name')->ignore($level?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'fee' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999.99'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'benefits' => ['nullable', 'string', 'max:5000'],
            'rank' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    public function destroy(MembershipLevel $level): RedirectResponse
    {
        $level->update(['is_active' => false]);

        return back()->with('success', 'Level deactivated. Existing records have been kept.');
    }
}
