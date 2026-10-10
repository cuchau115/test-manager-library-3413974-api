<?php

namespace Tests\Feature;

use App\Application\Ports\BookRepository;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;
use App\Infrastructure\Persistence\Models\BookModel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class PostgresSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_postgres_version_column_and_book_repository_write_are_live(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());
        $this->assertTrue(Schema::hasColumn('library.book', 'version'));

        $categoryId = DB::table('library.category')->value('id');
        $book = Book::create(
            (string) Uuid::uuid4(),
            'Version verification',
            'Integration test',
            Isbn::fromString('9780000000001'),
            $categoryId,
            2,
        );
        $repository = app(BookRepository::class);
        $repository->save($book);
        $this->assertSame(0, (int) BookModel::findOrFail($book->id())->version);

        $reloaded = $repository->findActiveById($book->id());
        $reloaded->changeTotal(3);
        $repository->save($reloaded);
        $this->assertSame(1, (int) BookModel::findOrFail($book->id())->version);
    }

    public function test_postgres_enforces_total_and_role_checks(): void
    {
        $categoryId = DB::table('library.category')->value('id');

        try {
            DB::transaction(function () use ($categoryId): void {
                DB::table('library.book')->insert([
                    'id' => (string) Uuid::uuid4(),
                    'title' => 'Invalid total',
                    'author' => 'Constraint check',
                    'isbn' => '9780000000002',
                    'category_id' => $categoryId,
                    'total_copies' => 0,
                    'available_copies' => 0,
                    'version' => 0,
                ]);
            });
            $this->fail('PostgreSQL should reject total_copies = 0.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('ck_book_total_positive', $exception->getMessage());
        }

        try {
            DB::transaction(function (): void {
                DB::table('library.user')->insert([
                    'id' => (string) Uuid::uuid4(),
                    'username' => 'invalid-role-' . Uuid::uuid4(),
                    'password_hash' => 'not-a-real-password-hash',
                    'role' => 'superuser',
                ]);
            });
            $this->fail('PostgreSQL should reject an unsupported role.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('ck_user_role', $exception->getMessage());
        }
    }
}
