<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'borrowed_at' => $this->borrowed_at,
            'due_at' => $this->due_at,
            'returned_at' => $this->returned_at,
            'fine_amount' => $this->fine_amount,
            'book' => $this->whenLoaded('book', fn () => [
                'id' => $this->book->id,
                'isbn' => $this->book->isbn,
                'title' => $this->book->title,
            ]),
            'member' => $this->whenLoaded('member', fn () => [
                'id' => $this->member->id,
                'membership_no' => $this->member->membership_no,
                'name' => $this->member->name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
