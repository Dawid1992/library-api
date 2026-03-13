<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // POST /api/books
    // -------------------------------------------------------------------------

    public function test_can_create_book_without_authors(): void
    {
        $payload = [
            'title'          => 'Władca Pierścieni',
            'isbn'           => '978-83-123456-7-8',
            'published_year' => 1954,
            'description'    => 'Epopeja fantasy',
        ];

        $response = $this->actingAs(User::factory()->create())
                         ->postJson('/api/books', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Władca Pierścieni']);

        $this->assertDatabaseHas('books', ['title' => 'Władca Pierścieni']);
    }

    public function test_can_create_book_with_authors(): void
    {
        $authors = Author::factory()->count(2)->create();

        $payload = [
            'title'      => 'Diuna',
            'author_ids' => $authors->pluck('id')->toArray(),
        ];

        $response = $this->actingAs(User::factory()->create())
                         ->postJson('/api/books', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Diuna']);

        $book = Book::where('title', 'Diuna')->first();
        $this->assertCount(2, $book->authors);
    }

    public function test_cannot_create_book_without_title(): void
    {
        $response = $this->actingAs(User::factory()->create())
                         ->postJson('/api/books', [
                             'isbn' => '978-83-000000-0-0',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_cannot_create_book_with_duplicate_isbn(): void
    {
        Book::factory()->create(['isbn' => '978-83-111111-1-1']);

        $response = $this->actingAs(User::factory()->create())
                         ->postJson('/api/books', [
                             'title' => 'Inna książka',
                             'isbn'  => '978-83-111111-1-1',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['isbn']);
    }

    public function test_cannot_create_book_with_nonexistent_author(): void
    {
        $response = $this->actingAs(User::factory()->create())
                         ->postJson('/api/books', [
                             'title'      => 'Testowa książka',
                             'author_ids' => [99999],
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['author_ids.0']);
    }

    public function test_cannot_create_book_with_future_published_year(): void
    {
        $response = $this->actingAs(User::factory()->create())
                         ->postJson('/api/books', [
                             'title'          => 'Książka z przyszłości',
                             'published_year' => date('Y') + 1,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['published_year']);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/books/{id}
    // -------------------------------------------------------------------------

    public function test_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_delete_book_removes_author_relations(): void
    {
        $author = Author::factory()->create();
        $book   = Book::factory()->create();
        $book->authors()->attach($author->id);

        $this->deleteJson("/api/books/{$book->id}");

        $this->assertDatabaseMissing('author_book', [
            'book_id'   => $book->id,
            'author_id' => $author->id,
        ]);
    }

    public function test_cannot_delete_nonexistent_book(): void
    {
        $response = $this->deleteJson('/api/books/99999');

        $response->assertStatus(404);
    }
}