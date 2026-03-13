<?php

namespace App\Logic;

use App\Models\Author;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IAuthorLogic
{
    public function getAll(?string $search = null, int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): Author;
    public function create(array $data): Author;
    public function update(int $id, array $data): Author;
    public function delete(int $id): void;
}
