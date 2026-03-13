<?php

namespace App\Http\Controllers;

use App\Logic\IBookLogic;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class BookController extends Controller
{
    public function __construct(
        private readonly IBookLogic $bookLogic,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);

        return BookResource::collection($this->bookLogic->getAll($perPage));
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->bookLogic->create($request->validated());

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResource
    {
        return new BookResource($this->bookLogic->findById($id));
    }

    public function update(UpdateBookRequest $request, int $id): JsonResource
    {
        $book = $this->bookLogic->update($id, $request->validated());

        return new BookResource($book);
    }

    public function destroy(int $id): Response
    {
        $this->bookLogic->delete($id);

        return response()->noContent();
    }
}