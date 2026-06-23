<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Entity\LoanHistory;
use App\Repository\LoanHistoryRepository;
use App\Security\CurrentEmployeeProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class ReturnBookAction
{
    public function __construct(
        private readonly CurrentEmployeeProvider $employeeProvider,
        private readonly LoanHistoryRepository $loanHistoryRepository,
    ) {}

    public function __invoke(Book $data, Request $request): Book
    {
        if (!$data->isBorrowed()) {
            throw new BadRequestHttpException('Operacja niemożliwa do wykonania.');
        }

        $employee = $this->employeeProvider->getCurrentEmployee();
        if (!$employee) {
            throw new BadRequestHttpException('Pracownik nie istnieje.');
        }

        $user = $data->getCurrentBorrower();
        $data->returnBook();

        $history = new LoanHistory($data, $user, $employee, LoanHistory::ACTION_RETURN);

        $this->loanHistoryRepository->save($history);

        return $data;
    }
}
