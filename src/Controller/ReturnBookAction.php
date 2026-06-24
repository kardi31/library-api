<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Event\BookReturnedEvent;
use App\Repository\BookRepository;
use App\Security\CurrentEmployeeProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class ReturnBookAction
{
    public function __construct(
        private readonly CurrentEmployeeProvider $employeeProvider,
        private readonly BookRepository $bookRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function __invoke(string $serialNumber): Book
    {
        $book = $this->bookRepository->findBySerialNumber($serialNumber);
        if (!$book) {
            throw new NotFoundHttpException('Podana książka nie istnieje');
        }

        $user = $book->getCurrentBorrower();
        if (!$book->isBorrowed() || $user === null) {
            throw new BadRequestHttpException('Operacja niemożliwa do wykonania.');
        }

        $employee = $this->employeeProvider->getCurrentEmployee();
        if (!$employee) {
            throw new BadRequestHttpException('Pracownik nie istnieje.');
        }

        $book->returnBook();

        $this->eventDispatcher->dispatch(
            new BookReturnedEvent(
                $book->getId(),
                $user->getId(),
                $employee->getId()
            ),
            BookReturnedEvent::NAME
        );

        return $book;
    }
}
