<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE library.book ADD COLUMN IF NOT EXISTS version BIGINT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE library.book ALTER COLUMN version DROP DEFAULT');
        DB::statement('CREATE INDEX IF NOT EXISTS ix_book_version ON library.book (version)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS library.ix_book_version');
        DB::statement('ALTER TABLE library.book DROP COLUMN IF EXISTS version');
    }
};
