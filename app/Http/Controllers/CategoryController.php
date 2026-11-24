<?php

namespace App\Http\Controllers;

use App\Models\MembershipCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories', ['categories' => MembershipCategory::query()->withCount('members')->orderBy('name')->paginate(12)]);
    }

    public function create(): View
    {
        return view('admin.category-form', ['category' => new MembershipCategory(['is_active' => true, 'fee' => 0])]);
    }

    public function edit(MembershipCategory $category): View
    {
        return view('admin.category-form', compact('category'));
    }

    public function store(Request $request): RedirectResponse
    {
        MembershipCategory::query()->create($this->validated($request));

        return to_route('member-categories')->with('success', 'Category created.');
    }

    public function update(Request $request, MembershipCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return to_route('member-categories')->with('success', 'Category updated.');
    }

    /** @return array{name: string, description?: ?string, fee: mixed, is_active: bool} */
    private function validated(Request $request, ?MembershipCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'fee' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999.99'],
            'is_active' => ['required', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    public function destroy(MembershipCategory $category): RedirectResponse
    {
        $category->update(['is_active' => false]);

        return back()->with('success', 'Category deactivated. Existing records have been kept.');
    }
}
