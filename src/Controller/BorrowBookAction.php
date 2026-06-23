<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Entity\LoanHistory;
use App\Repository\LoanHistoryRepository;
use App\Repository\UserRepository;
use App\Security\CurrentEmployeeProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class BorrowBookAction
{
    public function __construct(
        private readonly CurrentEmployeeProvider $employeeProvider,
        private readonly UserRepository $userRepository,
        private readonly LoanHistoryRepository $loanHistoryRepository,
    ) {
    }

    public function __invoke(Book $data, Request $request): Book
    {
        $payload = json_decode($request->getContent(), true);
        $cardNumber = $payload['card_number'] ?? null;

        if (!$cardNumber) {
            throw new BadRequestHttpException('Brak wymaganych danych.');
        }

        $employee = $this->employeeProvider->getCurrentEmployee();
        $user = $this->userRepository->findByCardNumber($cardNumber);
        if (!$employee) {
            throw new BadRequestHttpException('Pracownik nie znaleziony');
        }

        if (!$user) {
            throw new BadRequestHttpException('Użytkownik nie istnieje');
        }

        if ($data->isBorrowed()) {
            throw new BadRequestHttpException('Książka została już wypożyczona.');
        }

        $data->borrow($user);
        $history = new LoanHistory($data, $user, $employee, LoanHistory::ACTION_BORROW);

        $this->loanHistoryRepository->save($history);

        return $data;
    }
}
