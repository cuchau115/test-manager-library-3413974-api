<?php

namespace App\Application\Services;

use App\Application\Ports\LoanRepository;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Loan;

final class GetLoanService
{
    public function __construct(private readonly LoanRepository $loans)
    {
    }

    public function get(string $id): Loan
    {
        return $this->loans->findById($id) ?? throw ResourceNotFound::forResource('loan');
    }
}
