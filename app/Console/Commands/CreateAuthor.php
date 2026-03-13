<?php

namespace App\Console\Commands;

use App\Models\Author;
use Illuminate\Console\Command;

class CreateAuthor extends Command
{
    protected $signature = 'author:create';

    protected $description = 'Interaktywnie utwórz nowego autora';

    public function handle(): int
    {
        $this->info('=== Tworzenie nowego autora ===');

        $firstName = $this->ask('Imię autora');
        $lastName = $this->ask('Nazwisko autora');

        if (empty(trim($firstName)) || empty(trim($lastName))) {
            $this->error('Imię i nazwisko nie mogą być puste.');
            return self::FAILURE;
        }

        if (!$this->confirm("Czy chcesz utworzyć autora: {$firstName} {$lastName}?", true)) {
            $this->line('Anulowano.');
            return self::SUCCESS;
        }

        $author = Author::create([
            'first_name' => trim($firstName),
            'last_name'  => trim($lastName),
        ]);

        $this->info("Autor został utworzony (ID: {$author->id}): {$author->first_name} {$author->last_name}");

        return self::SUCCESS;
    }
}
