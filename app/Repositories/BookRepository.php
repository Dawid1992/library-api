<?php

namespace App\Repositories;

use App\Models\Book;
use App\Repositories\IBookRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookRepository implements IBookRepository
{
    public function getAll(int $perPage): LengthAwarePaginator
    {
        return Book::with('authors')->paginate($perPage);
    }

    public function findById(int $id): ?Book
    {
        return Book::with('authors')->find($id);
    }

    public function create(array $data): Book
    {
        return Book::create($data);
    }

    public function update(Book $book, array $data): Book
    {
        $book->update($data);
        return $book->fresh('authors');
    }

    public function delete(Book $book): void
    {
        $book->delete();
    }

    public function syncAuthors(Book $book, array $authorIds): void
    {
        $book->authors()->sync($authorIds);
    }
}
