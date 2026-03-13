<?php

namespace Tests\Unit;

use App\Jobs\UpdateAuthorLastAddedBook;
use App\Logic\BookLogic;
use App\Models\Book;
use App\Repositories\IBookRepository;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use App\Exceptions\ModelNotFoundByIdException;

class BookLogicTest extends TestCase
{
    private BookLogic $bookLogic;
    private IBookRepository&MockObject $bookRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookRepository = $this->createMock(IBookRepository::class);
        $this->bookLogic = new BookLogic($this->bookRepository);
    }

    private function makeBook(int $id, string $title): Book&MockObject
    {
        $book = $this->getMockBuilder(Book::class)
            ->onlyMethods(['load'])
            ->getMock();

        $book->id = $id;
        $book->title = $title;

        $book->method('load')->willReturnSelf();

        return $book;
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_saves_book_and_dispatches_job(): void
    {
        Queue::fake();

        $book = $this->makeBook(1, 'Diuna');

        $this->bookRepository
            ->expects($this->once())
            ->method('create')
            ->with(['title' => 'Diuna'])
            ->willReturn($book);

        $this->bookRepository
            ->expects($this->never())
            ->method('syncAuthors');

        $result = $this->bookLogic->create(['title' => 'Diuna']);

        $this->assertSame($book, $result);
        Queue::assertPushed(UpdateAuthorLastAddedBook::class);
    }

    public function test_create_syncs_authors_when_author_ids_provided(): void
    {
        Queue::fake();

        $book = $this->makeBook(1, 'Diuna');

        $this->bookRepository
            ->expects($this->once())
            ->method('create')
            ->with(['title' => 'Diuna'])
            ->willReturn($book);

        $this->bookRepository
            ->expects($this->once())
            ->method('syncAuthors')
            ->with($book, [1, 2]);

        $this->bookLogic->create(['title' => 'Diuna', 'author_ids' => [1, 2]]);

        Queue::assertPushed(UpdateAuthorLastAddedBook::class);
    }

    public function test_create_does_not_pass_author_ids_to_repository(): void
    {
        Queue::fake();

        $book = $this->makeBook(1, 'Diuna');

        $this->bookRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->logicalNot($this->arrayHasKey('author_ids')))
            ->willReturn($book);

        $this->bookLogic->create(['title' => 'Diuna', 'author_ids' => [1]]);
    }

    // -------------------------------------------------------------------------
    // delete()
    // -------------------------------------------------------------------------

    public function test_delete_removes_existing_book(): void
    {
        $book = $this->makeBook(1, 'Diuna');

        $this->bookRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($book);

        $this->bookRepository
            ->expects($this->once())
            ->method('delete')
            ->with($book);

        $this->bookLogic->delete(1);
    }

    public function test_delete_throws_exception_when_book_not_found(): void
    {
        $this->bookRepository
            ->expects($this->once())
            ->method('findById')
            ->with(99)
            ->willReturn(null);

        $this->expectException(ModelNotFoundByIdException::class);

        $this->bookLogic->delete(99);
    }

    // -------------------------------------------------------------------------
    // findById()
    // -------------------------------------------------------------------------

    public function test_find_by_id_returns_book(): void
    {
        $book = $this->makeBook(1, 'Diuna');

        $this->bookRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($book);

        $result = $this->bookLogic->findById(1);

        $this->assertSame($book, $result);
    }

    public function test_find_by_id_throws_exception_when_not_found(): void
    {
        $this->bookRepository
            ->expects($this->once())
            ->method('findById')
            ->with(99)
            ->willReturn(null);

        $this->expectException(ModelNotFoundByIdException::class);

        $this->bookLogic->findById(99);
    }
}