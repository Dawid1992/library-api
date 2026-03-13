<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IBookRepository
{
    public function getAll(int $perPage): LengthAwarePaginator;
    public function findById(int $id): ?Book;
    public function create(array $data): Book;
    public function update(Book $book, array $data): Book;
    public function delete(Book $book): void;
    public function syncAuthors(Book $book, array $authorIds): void;
}
