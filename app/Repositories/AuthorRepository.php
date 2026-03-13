<?php

namespace App\Repositories;

use App\Models\Author;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuthorRepository implements IAuthorRepository
{
    public function getAll(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Author::with('books');

        if ($search !== null) {
            $query->whereHas('books', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Author
    {
        return Author::with('books')->find($id);
    }

    public function create(array $data): Author
    {
        return Author::create($data);
    }

    public function update(Author $author, array $data): Author
    {
        $author->update($data);
        return $author->fresh();
    }

    public function delete(Author $author): void
    {
        $author->delete();
    }
}
