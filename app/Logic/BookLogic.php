<?php

namespace App\Logic;

use App\Models\Book;
use App\Repositories\IBookRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Jobs\UpdateAuthorLastAddedBook;
use App\Exceptions\ModelNotFoundByIdException;

class BookLogic implements IBookLogic
{
    public function __construct(
        private readonly IBookRepository $bookRepository,
    ) {}

    public function getAll(int $perPage): LengthAwarePaginator
    {
        return $this->bookRepository->getAll($perPage);
    }

    public function findById(int $id): Book
    {
        $book = $this->bookRepository->findById($id);

        if (!$book) {
            throw new ModelNotFoundByIdException('Book', $id);
        }

        return $book;
    }

    public function create(array $data): Book
    {
        $book = $this->bookRepository->create(
            collect($data)->except('author_ids')->toArray()
        );

        if (!empty($data['author_ids'])) {
            $this->bookRepository->syncAuthors($book, $data['author_ids']);
        }

        $book->load('authors');

        UpdateAuthorLastAddedBook::dispatch($book->id);

        return $book;
    }

    public function update(int $id, array $data): Book
    {
        $book = $this->findById($id);

        $book = $this->bookRepository->update(
            $book,
            collect($data)->except('author_ids')->toArray()
        );

        if (array_key_exists('author_ids', $data)) {
            $this->bookRepository->syncAuthors($book, $data['author_ids']);
        }

        return $book->load('authors');
    }

    public function delete(int $id): void
    {
        $book = $this->findById($id);
        $this->bookRepository->delete($book);
    }
}
