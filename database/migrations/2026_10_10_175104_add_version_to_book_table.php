<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library.book', function (Blueprint $table) {
            $table->unsignedBigInteger('version')->default(0)->after('deleted_at');
            $table->index('version', 'ix_book_version');
        });
    }

    public function down(): void
    {
        Schema::table('library.book', function (Blueprint $table) {
            $table->dropIndex('ix_book_version');
            $table->dropColumn('version');
        });
    }
};