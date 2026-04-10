<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:members.view')->only(['index', 'show']);
        $this->middleware('permission:members.create')->only('store');
        $this->middleware('permission:members.update')->only('update');
        $this->middleware('permission:members.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $members = Member::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $keyword = $request->string('search')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('email', 'like', '%'.$keyword.'%')
                        ->orWhere('membership_no', 'like', '%'.$keyword.'%');
                });
            })
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return MemberResource::collection($members);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'membership_no' => ['required', 'string', 'max:30', 'unique:members,membership_no'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'joined_at' => ['required', 'date'],
        ]);

        $member = Member::create($validated);

        return new MemberResource($member);
    }

    public function show(Member $member)
    {
        return new MemberResource($member->loadCount('loans'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'membership_no' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('members', 'membership_no')->ignore($member->id)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'joined_at' => ['sometimes', 'required', 'date'],
        ]);

        $member->update($validated);

        return new MemberResource($member);
    }

    public function destroy(Member $member)
    {
        if ($member->loans()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return response()->json([
                'message' => 'Member cannot be deleted while active loans exist.',
            ], 409);
        }

        $member->delete();

        return response()->json(status: 204);
    }
}
