<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_authors_matching_book_title(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['title' => 'Harry Potter']);
        $author->books()->attach($book);

        Author::factory()
            ->hasAttached(Book::factory()->create(['title' => 'Lord of the Rings']))
            ->create();

        $response = $this->getJson('/api/authors?search=harry');

        $response->assertOk()
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.id', $author->id);
    }

    public function test_returns_all_authors_without_search(): void
    {
        Author::factory(3)->create();

        $this->getJson('/api/authors')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }
}