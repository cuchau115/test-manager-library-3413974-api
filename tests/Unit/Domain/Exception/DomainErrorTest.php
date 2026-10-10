<?php

namespace Tests\Unit\Domain\Exception;

use App\Domain\Exception\BookNotAvailable;
use App\Domain\Exception\DomainError;
use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\InvalidDateRange;
use App\Domain\Exception\ItemAlreadyReturned;
use App\Domain\Exception\MemberHasOverdue;
use App\Domain\Exception\MemberLimitExceeded;
use App\Domain\Exception\ValidationError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DomainErrorTest extends TestCase
{
    public function test_base_es_abstracta_y_sin_framework(): void
    {
        $reflection = new \ReflectionClass(DomainError::class);

        $this->assertTrue($reflection->isAbstract());
        $this->assertStringNotContainsString('Illuminate', $reflection->getFileName());
        $this->assertTrue(is_subclass_of(DomainError::class, \RuntimeException::class));
    }

    /**
     * @return array<string, array{class-string<DomainError>, string}>
     */
    public static function errores(): array
    {
        return [
            'book not available' => [BookNotAvailable::class, 'BOOK_NOT_AVAILABLE'],
            'member has overdue' => [MemberHasOverdue::class, 'MEMBER_HAS_OVERDUE'],
            'member limit exceeded' => [MemberLimitExceeded::class, 'MEMBER_LIMIT_EXCEEDED'],
            'item already returned' => [ItemAlreadyReturned::class, 'ITEM_ALREADY_RETURNED'],
            'duplicate book in loan' => [DuplicateBookInLoan::class, 'DUPLICATE_BOOK_IN_LOAN'],
            'invalid date range' => [InvalidDateRange::class, 'INVALID_DATE_RANGE'],
            'validation' => [ValidationError::class, 'VALIDATION_ERROR'],
        ];
    }

    /**
     * @param class-string<DomainError> $class
     */
    #[DataProvider('errores')]
    public function test_codigo_de_negocio(string $class, string $code): void
    {
        $error = new $class('mensaje');

        $this->assertInstanceOf(DomainError::class, $error);
        $this->assertSame($code, $error->errorCode());
    }

    public function test_lleva_detalles(): void
    {
        $error = BookNotAvailable::forTitle('Cien años de soledad');

        $this->assertNotSame('', $error->getMessage());
        $this->assertNotEmpty($error->details());
    }
}
