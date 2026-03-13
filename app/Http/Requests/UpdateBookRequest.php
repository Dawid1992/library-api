<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['sometimes', 'string', 'max:255'],
            'isbn'           => ['nullable', 'string', 'max:20', 'unique:books,isbn,' . $this->route('id')],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'description'    => ['nullable', 'string'],
            'author_ids'     => ['nullable', 'array'],
            'author_ids.*'   => ['integer', 'exists:authors,id'],
        ];
    }
}
