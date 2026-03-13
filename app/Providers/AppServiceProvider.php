<?php

namespace App\Providers;

use App\Logic\AuthorLogic;
use App\Logic\BookLogic;
use App\Logic\IAuthorLogic;
use App\Logic\IBookLogic;
use App\Repositories\AuthorRepository;
use App\Repositories\BookRepository;
use App\Repositories\IAuthorRepository;
use App\Repositories\IBookRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IAuthorRepository::class, AuthorRepository::class);
        $this->app->bind(IBookRepository::class, BookRepository::class);
        $this->app->bind(IAuthorLogic::class, AuthorLogic::class);
        $this->app->bind(IBookLogic::class, BookLogic::class);
    }

    public function boot(): void
    {
        //
    }
}
