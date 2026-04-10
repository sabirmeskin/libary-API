<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
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
            'name' => $this->name,
            'bio' => $this->bio,
            'birth_date' => $this->birth_date,
            'country' => $this->country,
            'books_count' => $this->whenCounted('books'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
