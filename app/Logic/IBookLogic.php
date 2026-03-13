<?php

namespace App\Logic;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IBookLogic
{
    public function getAll(int $perPage): LengthAwarePaginator;
    public function findById(int $id): Book;
    public function create(array $data): Book;
    public function update(int $id, array $data): Book;
    public function delete(int $id): void;
}
