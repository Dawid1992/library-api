<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->foreignId('last_added_book_id')
                ->nullable()
                ->constrained('books')
                ->nullOnDelete()
                ->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropForeign(['last_added_book_id']);
            $table->dropColumn('last_added_book_id');
        });
    }
};