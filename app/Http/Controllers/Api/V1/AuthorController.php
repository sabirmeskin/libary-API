<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:authors.view')->only(['index', 'show']);
        $this->middleware('permission:authors.create')->only('store');
        $this->middleware('permission:authors.update')->only('update');
        $this->middleware('permission:authors.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $authors = Author::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('country'), fn ($query) => $query->where('country', $request->string('country')))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return AuthorResource::collection($authors);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $author = Author::create($validated);

        return new AuthorResource($author);
    }

    public function show(Author $author)
    {
        return new AuthorResource($author->loadCount('books'));
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $author->update($validated);

        return new AuthorResource($author);
    }

    public function destroy(Author $author)
    {
        if ($author->books()->exists()) {
            return response()->json([
                'message' => 'Author cannot be deleted while books are linked to it.',
            ], 409);
        }

        $author->delete();

        return response()->json(status: 204);
    }
}
