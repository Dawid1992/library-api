<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'          => ['sometimes', 'string', 'max:255'],
            'last_name'           => ['sometimes', 'string', 'max:255'],
            'bio'                 => ['nullable', 'string'],
            'last_added_book_id'  => ['nullable', 'integer', 'exists:books,id'],
        ];
    }
}
