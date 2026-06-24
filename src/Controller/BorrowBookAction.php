<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\UserRepository;
use App\Security\CurrentEmployeeProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class BorrowBookAction
{
    public function __construct(
        private readonly CurrentEmployeeProvider $employeeProvider,
        private readonly UserRepository $userRepository,
        private readonly BookRepository $bookRepository,
    ) {
    }

    public function __invoke(string $serialNumber, Request $request): Book
    {
        $book = $this->bookRepository->findBySerialNumber($serialNumber);
        if (!$book) {
            throw new NotFoundHttpException('Podana książka nie istnieje');
        }
        $payload = json_decode($request->getContent(), true);
        $cardNumber = $payload['card_number'] ?? null;

        if (!$cardNumber) {
            throw new BadRequestHttpException('Brak wymaganych danych.');
        }

        $employee = $this->employeeProvider->getCurrentEmployee();
        if (!$employee) {
            throw new BadRequestHttpException('Pracownik nie znaleziony');
        }

        $user = $this->userRepository->findByCardNumber($cardNumber);
        if (!$user) {
            throw new BadRequestHttpException('Użytkownik nie istnieje');
        }

        if ($book->isBorrowed()) {
            throw new BadRequestHttpException('Książka została już wypożyczona.');
        }

        $book->borrow($user);

        return $book;
    }
}
