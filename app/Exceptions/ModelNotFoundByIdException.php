<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class ModelNotFoundByIdException extends \RuntimeException
{
    public function __construct(string $modelName, int $id)
    {
        parent::__construct("{$modelName} with ID {$id} not found.");
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 404);
    }
}