<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:books.view')->only(['index', 'show']);
        $this->middleware('permission:books.create')->only('store');
        $this->middleware('permission:books.update')->only('update');
        $this->middleware('permission:books.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortBy = $request->string('sort_by', 'id')->toString();
        $sortDirection = $request->string('sort_direction', 'desc')->toString();
        $allowedSorts = ['id', 'title', 'published_year', 'available_copies', 'created_at'];

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $books = Book::query()
            ->with(['author', 'categories'])
            ->withCount('loans')
            ->when($request->filled('search'), function ($query) use ($request) {
                $keyword = $request->string('search')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('title', 'like', '%'.$keyword.'%')
                        ->orWhere('subtitle', 'like', '%'.$keyword.'%')
                        ->orWhere('isbn', 'like', '%'.$keyword.'%');
                });
            })
            ->when($request->filled('author_id'), fn ($query) => $query->where('author_id', $request->integer('author_id')))
            ->when($request->filled('category_id'), fn ($query) => $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->integer('category_id'))))
            ->when($request->filled('published_year'), fn ($query) => $query->where('published_year', $request->integer('published_year')))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return BookResource::collection($books);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'isbn' => ['required', 'string', 'max:20', 'unique:books,isbn'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'published_year' => ['nullable', 'integer', 'digits:4'],
            'total_copies' => ['required', 'integer', 'min:1', 'max:1000000'],
            'available_copies' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'shelf_location' => ['nullable', 'string', 'max:50'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $validated['available_copies'] = min(
            (int) ($validated['available_copies'] ?? $validated['total_copies']),
            (int) $validated['total_copies']
        );

        $book = Book::create($validated);

        if ($request->filled('category_ids')) {
            $book->categories()->sync($request->input('category_ids'));
        }

        return new BookResource($book->load(['author', 'categories']));
    }

    public function show(Book $book)
    {
        return new BookResource(
            $book->load(['author', 'categories'])->loadCount('loans')
        );
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'author_id' => ['sometimes', 'required', 'integer', 'exists:authors,id'],
            'isbn' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book->id)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'published_year' => ['nullable', 'integer', 'digits:4'],
            'total_copies' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000000'],
            'available_copies' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000000'],
            'shelf_location' => ['nullable', 'string', 'max:50'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        if (isset($validated['total_copies']) && isset($validated['available_copies']) && $validated['available_copies'] > $validated['total_copies']) {
            return response()->json([
                'message' => 'Available copies cannot be greater than total copies.',
            ], 422);
        }

        $book->update($validated);

        if ($request->has('category_ids')) {
            $book->categories()->sync($request->input('category_ids', []));
        }

        return new BookResource($book->load(['author', 'categories']));
    }

    public function destroy(Book $book)
    {
        if ($book->loans()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return response()->json([
                'message' => 'Book cannot be deleted while active loans exist.',
            ], 409);
        }

        $book->categories()->detach();
        $book->delete();

        return response()->json(status: 204);
    }
}
