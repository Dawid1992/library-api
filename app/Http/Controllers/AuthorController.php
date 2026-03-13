<?php

namespace App\Http\Controllers;

use App\Logic\IAuthorLogic;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\AuthorResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class AuthorController extends Controller
{
    public function __construct(
        private readonly IAuthorLogic $authorLogic,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $authors = $this->authorLogic->getAll($search, $perPage);

        return AuthorResource::collection($authors);
    }

    public function store(StoreAuthorRequest $request): JsonResponse
    {
        $author = $this->authorLogic->create($request->validated());

        return (new AuthorResource($author))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResource
    {
        $author = $this->authorLogic->findById($id);

        return new AuthorResource($author);
    }

    public function update(UpdateAuthorRequest $request, int $id): JsonResource
    {
        $author = $this->authorLogic->update($id, $request->validated());

        return new AuthorResource($author);
    }

    public function destroy(int $id): Response
    {
        $this->authorLogic->delete($id);

        return response()->noContent();
    }
}