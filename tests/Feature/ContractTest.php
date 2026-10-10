<?php

namespace Tests\Feature;

use App\Application\Ports\CoverStorage;
use App\Application\Ports\PasswordHasher;
use App\Infrastructure\Persistence\Models\UserModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Support\Fakes\FakeCoverStorage;

final class ContractTest extends TestCase
{
    use DatabaseTransactions;

    private const CATEGORY_ID = '11111111-1111-4111-8111-111111111111';

    private string $adminUsername;
    private string $adminPassword = 'admin-password';
    private string $adminToken;
    private int $isbnSequence = 1;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.env' => 'production']);

        $this->adminUsername = 'contract-' . strtolower(Uuid::uuid4()->toString());
        UserModel::create([
            'id' => Uuid::uuid4()->toString(),
            'username' => $this->adminUsername,
            'password_hash' => app(PasswordHasher::class)->hash($this->adminPassword),
            'role' => 'admin',
        ]);
    }

    public function test_http_contract_routes_statuses_and_payloads(): void
    {
        $this->getJson('/health')->assertOk()->assertExactJson(['status' => 'ok']);
        $this->getJson('/api/v1/health')->assertOk()->assertExactJson(['status' => 'ok']);
        $this->getJson('/api/v1/categories')->assertUnauthorized()
            ->assertJsonPath('code', 'INVALID_CREDENTIALS')
            ->assertJsonStructure(['code', 'message', 'details']);
        $this->postJson('/api/v1/auth/login', ['username' => 'missing', 'password' => 'wrong'])
            ->assertUnauthorized()->assertJsonPath('code', 'INVALID_CREDENTIALS');

        $login = $this->postJson('/api/v1/auth/login', [
            'username' => $this->adminUsername,
            'password' => $this->adminPassword,
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('expires_in', 28800)
            ->assertJsonPath('role', 'admin');
        $this->adminToken = $login->json('access_token');
        $this->withToken($this->adminToken);

        $this->getJson('/api/v1/categories')->assertOk()
            ->assertJsonCount(5, 'items')
            ->assertJsonStructure(['items' => [['id', 'name']], 'page', 'size', 'total']);

        $librarian = $this->postJson('/api/v1/users', [
            'username' => ' Ana ',
            'password' => 'librarian-password',
            'role' => 'librarian',
        ])->assertCreated()
            ->assertJsonPath('username', 'ana')
            ->assertJsonPath('role', 'librarian')
            ->assertJsonMissingPath('password')
            ->assertJsonMissingPath('password_hash');
        $this->postJson('/api/v1/users', [
            'username' => 'ana',
            'password' => 'librarian-password',
            'role' => 'librarian',
        ])->assertConflict()->assertJsonPath('code', 'USERNAME_TAKEN');
        $librarianLogin = $this->postJson('/api/v1/auth/login', [
            'username' => 'ANA ',
            'password' => 'librarian-password',
        ])->assertOk();
        $librarianToken = $librarianLogin->json('access_token');

        $this->withToken($librarianToken)->postJson('/api/v1/books', [])
            ->assertForbidden()->assertJsonPath('code', 'FORBIDDEN_ROLE');
        $this->withToken($librarianToken)->postJson('/api/v1/members', [
            'full_name' => 'Socio de prueba',
            'document_number' => 'DOC-CONTRACT-1',
        ])->assertCreated();

        $member = $this->createMember('DOC-CONTRACT-2');
        $this->postJson('/api/v1/members', [
            'full_name' => 'Duplicado',
            'document_number' => 'DOC-CONTRACT-2',
        ])->assertConflict()->assertJsonPath('code', 'DOCUMENT_TAKEN');
        $memberList = $this->getJson('/api/v1/members?q=Socio&page=1&size=10');
        $memberList->assertOk()
            ->assertJsonStructure(['items', 'page', 'size', 'total']);
        $this->getJson('/api/v1/members/' . $member['id'])->assertOk()
            ->assertJsonPath('active_loans', 0);

        $this->withToken($this->adminToken);
        $this->postJson('/api/v1/books', [])->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['code', 'message', 'details']);
        $book = $this->createBook();
        $this->postJson('/api/v1/books', $this->bookPayload($book['isbn']))
            ->assertConflict()->assertJsonPath('code', 'ISBN_TAKEN');
        $this->getJson('/api/v1/books/' . $book['id'])->assertOk()
            ->assertJsonPath('category.id', self::CATEGORY_ID)
            ->assertJsonPath('available_copies', 2);
        $this->getJson('/api/v1/books?q=Contract&page=1&size=10')->assertOk()
            ->assertJsonPath('total', 1);
        $this->putJson('/api/v1/books/' . $book['id'], [
            'title' => 'Contract Updated',
            'author' => 'Author',
            'category_id' => self::CATEGORY_ID,
            'total_copies' => 3,
        ])->assertOk()->assertJsonPath('title', 'Contract Updated');

        $storage = new FakeCoverStorage();
        $this->app->instance(CoverStorage::class, $storage);
        $upload = $this->put('/api/v1/books/' . $book['id'] . '/cover', [
            'file' => UploadedFile::fake()->image('cover.png'),
        ], ['Authorization' => 'Bearer ' . $this->adminToken])->assertOk();
        $coverKey = $upload->json('cover_key');
        $this->assertNotEmpty($coverKey);
        $this->get('/api/v1/books/' . $book['id'] . '/cover', [
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->deleteJson('/api/v1/books/' . $book['id'] . '/cover')->assertNoContent();
        $this->assertContains($coverKey, $storage->deleted);
        $this->getJson('/api/v1/books/' . $book['id'] . '/cover')->assertNotFound()
            ->assertJsonPath('code', 'NOT_FOUND');

        $this->withToken($this->adminToken);
        $loanBook = $this->createBook(total: 1);
        $loanResponse = $this->postJson('/api/v1/loans', [
            'member_id' => $member['id'],
            'items' => [['book_id' => $loanBook['id']]],
            'loaned_at' => '2020-01-01T00:00:00Z',
        ])->assertCreated()->assertJsonPath('status', 'open');
        $loan = $loanResponse->json();
        $this->assertEqualsWithDelta(
            time(),
            (new DateTimeImmutable($loan['loaned_at']))->getTimestamp(),
            10,
            'loaned_at from the non-local request must be ignored',
        );
        $this->postJson('/api/v1/loans', [
            'member_id' => $member['id'],
            'items' => [['book_id' => $loanBook['id']], ['book_id' => $loanBook['id']]],
        ])->assertUnprocessable()->assertJsonPath('code', 'DUPLICATE_BOOK_IN_LOAN');

        $this->getJson('/api/v1/loans/' . $loan['id'])->assertOk()
            ->assertJsonPath('items.0.book_id', $loanBook['id']);
        $this->getJson('/api/v1/loans?status=open&member_id=' . $member['id'])->assertOk()
            ->assertJsonPath('total', 1);
        $this->postJson('/api/v1/loans/' . $loan['id'] . '/items/' . $loan['items'][0]['id'] . '/return')
            ->assertOk()->assertJsonStructure(['id', 'book_id', 'returned_at']);
        $this->postJson('/api/v1/loans/' . $loan['id'] . '/items/' . $loan['items'][0]['id'] . '/return')
            ->assertConflict()->assertJsonPath('code', 'ITEM_ALREADY_RETURNED');

        $openBook = $this->createBook(total: 1);
        $openLoan = $this->postJson('/api/v1/loans', [
            'member_id' => $member['id'],
            'items' => [['book_id' => $openBook['id']]],
        ])->assertCreated()->json();
        $this->deleteJson('/api/v1/books/' . $openBook['id'])->assertConflict()
            ->assertJsonPath('code', 'BOOK_HAS_OPEN_LOANS');

        $fullMember = $this->createMember('DOC-CONTRACT-LIMIT');
        $limitBooks = [$this->createBook(total: 1), $this->createBook(total: 1), $this->createBook(total: 1), $this->createBook(total: 1)];
        $this->postJson('/api/v1/loans', [
            'member_id' => $fullMember['id'],
            'items' => array_map(fn ($item) => ['book_id' => $item['id']], array_slice($limitBooks, 0, 3)),
        ])->assertCreated();
        $this->postJson('/api/v1/loans', [
            'member_id' => $fullMember['id'],
            'items' => [['book_id' => $limitBooks[3]['id']]],
        ])->assertUnprocessable()->assertJsonPath('code', 'MEMBER_LIMIT_EXCEEDED');

        $otherMember = $this->createMember('DOC-CONTRACT-UNAVAILABLE');
        $this->postJson('/api/v1/loans', [
            'member_id' => $otherMember['id'],
            'items' => [['book_id' => $openBook['id']]],
        ])->assertConflict()->assertJsonPath('code', 'BOOK_NOT_AVAILABLE');
        $this->postJson('/api/v1/loans/' . $openLoan['id'] . '/items/' . $openLoan['items'][0]['id'] . '/return')
            ->assertOk();

        config(['app.env' => 'local']);
        $overdueLoans = [];
        for ($index = 1; $index <= 3; $index++) {
            $overdueMember = $this->createMember('DOC-CONTRACT-OVERDUE-' . $index);
            $overdueBook = $this->createBook(total: 1);
            $overdueLoans[] = $this->postJson('/api/v1/loans', [
                'member_id' => $overdueMember['id'],
                'items' => [['book_id' => $overdueBook['id']]],
                'loaned_at' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))
                    ->modify('-15 days')->format('Y-m-d\TH:i:s\Z'),
            ])->assertCreated()->assertJsonPath('status', 'overdue')->json();
        }
        $this->getJson('/api/v1/loans/overdue?page=1&size=10')->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonStructure(['items' => [['loan_id', 'item_id', 'book_title', 'member_name', 'due_at', 'days_late']]]);
        $this->getJson('/api/v1/members/' . $overdueLoans[0]['member_id'])->assertOk()
            ->assertJsonPath('active_loans', 1);
        $this->postJson('/api/v1/loans', [
            'member_id' => $overdueLoans[0]['member_id'],
            'items' => [['book_id' => $this->createBook(total: 1)['id']]],
        ])->assertConflict()->assertJsonPath('code', 'MEMBER_HAS_OVERDUE');

        $this->getJson('/api/v1/reports/most-borrowed?from=2020-01-01&to=2035-01-01')->assertOk()
            ->assertJsonStructure(['from', 'to', 'rows'])
            ->assertJsonMissingPath('rows.0.member_name');
        $this->getJson('/api/v1/reports/most-borrowed?from=2030-01-01&to=2020-01-01')
            ->assertUnprocessable()->assertJsonPath('code', 'INVALID_DATE_RANGE');
        $this->withToken($librarianToken)
            ->getJson('/api/v1/reports/most-borrowed?from=2020-01-01&to=2035-01-01')
            ->assertForbidden()->assertJsonPath('code', 'FORBIDDEN_ROLE');

        $missing = Uuid::uuid4()->toString();
        $this->withToken($this->adminToken)->getJson('/api/v1/books/' . $missing)
            ->assertNotFound()->assertJsonPath('code', 'NOT_FOUND');
        $this->getJson('/api/v1/loans/' . $missing)->assertNotFound()->assertJsonPath('code', 'NOT_FOUND');
        $this->deleteJson('/api/v1/books/' . $openBook['id'])->assertNoContent();
        $this->getJson('/api/v1/books/' . $openBook['id'])->assertNotFound()
            ->assertJsonPath('code', 'NOT_FOUND');
    }

    private function createMember(string $documentNumber): array
    {
        return $this->postJson('/api/v1/members', [
            'full_name' => 'Socio ' . $documentNumber,
            'document_number' => $documentNumber,
        ])->assertCreated()->json();
    }

    private function createBook(int $total = 2): array
    {
        return $this->postJson('/api/v1/books', $this->bookPayload($this->isbn(), $total))
            ->assertCreated()->json();
    }

    private function bookPayload(string $isbn, int $total = 2): array
    {
        return [
            'title' => 'Contract Book ' . $this->isbnSequence,
            'author' => 'Contract Author',
            'isbn' => $isbn,
            'category_id' => self::CATEGORY_ID,
            'total_copies' => $total,
        ];
    }

    private function isbn(): string
    {
        return sprintf('978%010d', $this->isbnSequence++);
    }
}
