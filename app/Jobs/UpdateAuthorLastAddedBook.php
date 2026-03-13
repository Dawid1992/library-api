<?php

namespace App\Jobs;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateAuthorLastAddedBook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $bookId,
    ) {}

    public function handle(): void
    {
        $book = Book::find($this->bookId);

        if (!$book) {
            return;
        }

        Author::whereHas('books', fn($q) => $q->where('books.id', $this->bookId))
            ->update(['last_added_book_id' => $this->bookId]);
    }
}