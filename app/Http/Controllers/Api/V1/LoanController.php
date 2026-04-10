<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Models\Book;
use App\Models\Loan;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:loans.view')->only(['index', 'show']);
        $this->middleware('permission:loans.create')->only('store');
        $this->middleware('permission:loans.update')->only(['update', 'markReturned']);
        $this->middleware('permission:loans.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $loans = Loan::query()
            ->with(['book', 'member'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('member_id'), fn ($query) => $query->where('member_id', $request->integer('member_id')))
            ->when($request->filled('book_id'), fn ($query) => $query->where('book_id', $request->integer('book_id')))
            ->when($request->boolean('overdue'), fn ($query) => $query->whereIn('status', ['borrowed', 'overdue'])->where('due_at', '<', now()))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return LoanResource::collection($loans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'borrowed_at' => ['nullable', 'date'],
            'due_at' => ['required', 'date', 'after:borrowed_at'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $loan = DB::transaction(function () use ($validated) {
            $book = Book::query()->lockForUpdate()->findOrFail($validated['book_id']);

            if ($book->available_copies < 1) {
                throw new ConflictHttpException('No available copy for this book.');
            }

            $duplicateActiveLoan = Loan::query()
                ->where('book_id', $validated['book_id'])
                ->where('member_id', $validated['member_id'])
                ->whereIn('status', ['borrowed', 'overdue'])
                ->exists();

            if ($duplicateActiveLoan) {
                throw new ConflictHttpException('This member already has an active loan for the selected book.');
            }

            $loan = Loan::create([
                'book_id' => $validated['book_id'],
                'member_id' => $validated['member_id'],
                'borrowed_at' => $validated['borrowed_at'] ?? now(),
                'due_at' => $validated['due_at'],
                'status' => 'borrowed',
                'fine_amount' => $validated['fine_amount'] ?? 0,
            ]);

            $book->decrement('available_copies');

            return $loan;
        });

        return new LoanResource($loan->load(['book', 'member']));
    }

    public function show(Loan $loan)
    {
        return new LoanResource($loan->load(['book', 'member']));
    }

    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'due_at' => ['sometimes', 'required', 'date', 'after:borrowed_at'],
            'status' => ['sometimes', 'required', Rule::in(['borrowed', 'returned', 'overdue', 'lost'])],
            'fine_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'returned_at' => ['nullable', 'date'],
        ]);

        if (($validated['status'] ?? null) === 'returned' && empty($validated['returned_at'])) {
            $validated['returned_at'] = now();
        }

        $loan->update($validated);

        return new LoanResource($loan->load(['book', 'member']));
    }

    public function markReturned(Loan $loan)
    {
        if ($loan->status === 'returned') {
            return response()->json([
                'message' => 'Loan is already returned.',
            ], 422);
        }

        DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => 'returned',
                'returned_at' => now(),
            ]);

            Book::query()->lockForUpdate()->findOrFail($loan->book_id)->increment('available_copies');
        });

        return new LoanResource($loan->fresh()->load(['book', 'member']));
    }

    public function destroy(Loan $loan)
    {
        if (in_array($loan->status, ['borrowed', 'overdue'], true)) {
            return response()->json([
                'message' => 'Active loans cannot be deleted.',
            ], 409);
        }

        $loan->delete();

        return response()->json(status: 204);
    }
}
