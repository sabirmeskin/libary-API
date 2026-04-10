<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\MemberResource;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:search.global');
    }

    public function global(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($validated['limit'] ?? 10);
        $term = $validated['q'];

        $books = Book::query()
            ->with(['author', 'categories'])
            ->where(function ($query) use ($term): void {
                $query->where('title', 'like', '%'.$term.'%')
                    ->orWhere('subtitle', 'like', '%'.$term.'%')
                    ->orWhere('isbn', 'like', '%'.$term.'%');
            })
            ->limit($limit)
            ->get();

        $members = Member::query()
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('membership_no', 'like', '%'.$term.'%');
            })
            ->limit($limit)
            ->get();

        return response()->json([
            'query' => $term,
            'books' => BookResource::collection($books),
            'members' => MemberResource::collection($members),
            'counts' => [
                'books' => $books->count(),
                'members' => $members->count(),
            ],
        ]);
    }

    public function books(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 20);
        $term = $validated['q'];

        $books = Book::query()
            ->with(['author', 'categories'])
            ->where(function ($query) use ($term): void {
                $query->where('title', 'like', '%'.$term.'%')
                    ->orWhere('subtitle', 'like', '%'.$term.'%')
                    ->orWhere('isbn', 'like', '%'.$term.'%');
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return BookResource::collection($books);
    }

    public function members(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 20);
        $term = $validated['q'];

        $members = Member::query()
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('membership_no', 'like', '%'.$term.'%');
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return MemberResource::collection($members);
    }
}
