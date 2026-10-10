<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\BookRepository;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;
use App\Infrastructure\Persistence\Models\BookModel;
use App\Infrastructure\Persistence\Models\CategoryModel;
use DateTimeImmutable;

final class EloquentBookRepository implements BookRepository
{
    /** @var array<string, int> */
    private array $versions = [];

    public function nextIdentity(): string
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }

    public function save(Book $book): void
    {
        $model = BookModel::find($book->id());
        if ($model === null) {
            $model = new BookModel();
            $model->id = $book->id();
            $model->version = 0;
        }

        $model->title = $book->title();
        $model->author = $book->author();
        $model->isbn = $book->isbn()->value();
        $model->category_id = $book->categoryId();
        $model->total_copies = $book->total();
        $model->available_copies = $book->available();
        $model->cover_key = $book->coverKey();
        $model->deleted_at = $book->deletedAt();

        // Use cached version for optimistic locking
        $version = $this->versions[$book->id()] ?? $model->version;

        // Optimistic locking: increment version and check for conflicts
        $affected = BookModel::where('id', $book->id())
            ->where('version', $version)
            ->update([
                'title' => $model->title,
                'author' => $model->author,
                'isbn' => $model->isbn,
                'category_id' => $model->category_id,
                'total_copies' => $model->total_copies,
                'available_copies' => $model->available_copies,
                'cover_key' => $model->cover_key,
                'deleted_at' => $model->deleted_at,
                'version' => $version + 1,
            ]);

        if ($affected === 0) {
            throw new \App\Domain\Exception\BookNotAvailable('El libro ha sido modificado por otro proceso. Intente de nuevo.');
        }

        // Update cached version
        $this->versions[$book->id()] = $version + 1;
    }

    public function findById(string $id): ?Book
    {
        $model = BookModel::with('category')->find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findActiveById(string $id): ?Book
    {
        $model = BookModel::with('category')
            ->whereNull('deleted_at')
            ->find($id);
        if ($model === null) {
            return null;
        }
        $this->versions[$id] = $model->version;
        return $this->toDomain($model);
    }

    public function findActiveByIsbn(string $isbn): ?Book
    {
        $model = BookModel::with('category')
            ->whereNull('deleted_at')
            ->where('isbn', $isbn)
            ->first();
        if ($model === null) {
            return null;
        }
        $this->versions[$model->id] = $model->version;
        return $this->toDomain($model);
    }

    /**
     * @param list<string> $ids
     * @return list<Book>
     */
    public function findManyActiveByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $models = BookModel::with('category')
            ->whereNull('deleted_at')
            ->whereIn('id', $ids)
            ->get();

        return $models->map(fn ($m) => $this->toDomain($m))->all();
    }

    /**
     * @return array{items: list<Book>, total: int}
     */
    public function search(?string $text, ?string $categoryId, int $page, int $size): array
    {
        $query = BookModel::with('category')
            ->whereNull('deleted_at')
            ->orderBy('title');

        if ($text !== null && $text !== '') {
            $query->where(function ($q) use ($text) {
                $q->where('title', 'like', "%{$text}%")
                  ->orWhere('author', 'like', "%{$text}%")
                  ->orWhere('isbn', 'like', "%{$text}%");
            });
        }

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        $total = $query->count();
        $models = $query->skip(($page - 1) * $size)->take($size)->get();

        return [
            'items' => $models->map(fn ($m) => $this->toDomain($m))->all(),
            'total' => $total,
        ];
    }

    public function hasOpenLoans(string $bookId): bool
    {
        return \App\Infrastructure\Persistence\Models\LoanItemModel::where('book_id', $bookId)
            ->whereNull('returned_at')
            ->exists();
    }

    private function toDomain(BookModel $model): Book
    {
        return Book::reconstitute(
            $model->id,
            $model->title,
            $model->author,
            new Isbn($model->isbn),
            $model->category_id,
            $model->total_copies,
            $model->available_copies,
            $model->cover_key,
            $model->deleted_at ? new DateTimeImmutable($model->deleted_at) : null,
        );
    }
}