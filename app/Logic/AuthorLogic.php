<?php

namespace App\Logic;

use App\Models\Author;
use App\Repositories\IAuthorRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Exceptions\ModelNotFoundByIdException;

class AuthorLogic implements IAuthorLogic
{
    public function __construct(
        private readonly IAuthorRepository $authorRepository,
    ) {}

    public function getAll(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->authorRepository->getAll($search, $perPage);
    }

    public function findById(int $id): Author
    {
        $author = $this->authorRepository->findById($id);

        if (!$author) {
            throw new ModelNotFoundByIdException('Author', $id);
        }

        return $author;
    }

    public function create(array $data): Author
    {
        return $this->authorRepository->create($data);
    }

    public function update(int $id, array $data): Author
    {
        $author = $this->findById($id);
        return $this->authorRepository->update($author, $data);
    }

    public function delete(int $id): void
    {
        $author = $this->findById($id);
        $this->authorRepository->delete($author);
    }
}
