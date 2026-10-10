<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\CreateBookService;
use App\Application\Services\DeleteBookService;
use App\Application\Services\GetBookService;
use App\Application\Services\RemoveCoverService;
use App\Application\Services\SearchBooksService;
use App\Application\Services\SetCoverService;
use App\Application\Services\UpdateBookService;
use App\Application\Ports\CategoryReader;
use App\Application\Ports\CoverStorage;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Book;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\BookSearchRequest;
use App\Http\Requests\Book\CreateBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Requests\Book\CoverRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    public function __construct(
        private readonly CreateBookService $createBookService,
        private readonly SearchBooksService $searchBooksService,
        private readonly GetBookService $getBookService,
        private readonly UpdateBookService $updateBookService,
        private readonly DeleteBookService $deleteBookService,
        private readonly SetCoverService $setCoverService,
        private readonly RemoveCoverService $removeCoverService,
        private readonly CategoryReader $categories,
        private readonly CoverStorage $covers,
    ) {}

    public function index(BookSearchRequest $request): JsonResponse
    {
        $result = $this->searchBooksService->search(
            $request->validated('q'),
            $request->validated('category_id'),
            (int) $request->validated('page', 1),
            (int) $request->validated('size', 20),
        );

        $items = array_map(fn (Book $book) => $this->bookData($book), $result['items']);

        return response()->json([
            'items' => $items,
            'page' => (int) $request->validated('page', 1),
            'size' => (int) $request->validated('size', 20),
            'total' => $result['total'],
        ]);
    }

    public function store(CreateBookRequest $request): JsonResponse
    {
        $book = $this->createBookService->create(
            $request->validated('title'),
            $request->validated('author'),
            $request->validated('isbn'),
            $request->validated('category_id'),
            $request->validated('total_copies'),
        );

        return response()->json($this->bookData($book), Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->bookData($this->getBookService->get($id)));
    }

    public function update(UpdateBookRequest $request, string $id): JsonResponse
    {
        $book = $this->updateBookService->update(
            $id,
            $request->validated('title'),
            $request->validated('author'),
            $request->validated('category_id'),
            $request->integer('total_copies'),
        );

        return response()->json($this->bookData($book));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->deleteBookService->delete($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function updateCover(CoverRequest $request, string $id): JsonResponse
    {
        $file = $request->file('file');
        $book = $this->setCoverService->set(
            $id,
            $file->getContent(),
            $file->getMimeType(),
        );

        return response()->json(['cover_key' => $book->coverKey()]);
    }

    public function deleteCover(string $id): JsonResponse
    {
        $this->removeCoverService->remove($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function cover(string $id): Response
    {
        $book = $this->getBookService->get($id);
        $key = $book->coverKey() ?? throw ResourceNotFound::forResource('cover');
        $contents = $this->covers->get($key) ?? throw ResourceNotFound::forResource('cover');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: 'application/octet-stream';

        return response($contents, Response::HTTP_OK, ['Content-Type' => $mime]);
    }

    private function bookData(Book $book): array
    {
        if (!isset($this->categoryNames)) {
            $this->categoryNames = [];
            foreach ($this->categories->all() as $category) {
                $this->categoryNames[$category['id']] = $category['name'];
            }
        }

        return [
            'id' => $book->id(),
            'title' => $book->title(),
            'author' => $book->author(),
            'isbn' => $book->isbn()->value(),
            'category' => [
                'id' => $book->categoryId(),
                'name' => $this->categoryNames[$book->categoryId()] ?? null,
            ],
            'total_copies' => $book->total(),
            'available_copies' => $book->available(),
            'cover_key' => $book->coverKey(),
        ];
    }

    /** @var array<string, string>|null */
    private ?array $categoryNames = null;
}