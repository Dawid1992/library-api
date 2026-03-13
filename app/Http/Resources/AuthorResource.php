<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,

            'bio' => $this->bio,

            'last_added_book_id' => $this->last_added_book_id,

            'last_added_book' => $this->whenLoaded('lastAddedBook', fn() => new BookResource($this->lastAddedBook)),

            'books' => BookResource::collection(
                $this->whenLoaded('books')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}