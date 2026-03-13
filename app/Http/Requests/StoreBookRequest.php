<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'isbn'           => ['nullable', 'string', 'max:20', 'unique:books,isbn'],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'description'    => ['nullable', 'string'],
            'author_ids'     => ['nullable', 'array'],
            'author_ids.*'   => ['integer', 'exists:authors,id'],
        ];
    }
}
