<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\LoanHistory;
use App\Event\BookReturnedEvent;
use App\Repository\BookRepository;
use App\Repository\LoanHistoryRepository;
use App\Repository\UserRepository;
use App\Repository\EmployeeRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BookReturnedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly UserRepository $userRepository,
        private readonly EmployeeRepository $employeeRepository,
        private readonly LoanHistoryRepository $loanHistoryRepository,
        private readonly LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BookReturnedEvent::NAME => 'onBookReturned',
        ];
    }

    public function onBookReturned(BookReturnedEvent $event): void
    {
        $book = $this->bookRepository->find($event->getBookId());
        $user = $this->userRepository->find($event->getUserId());
        $employee = $this->employeeRepository->find($event->getEmployeeId());

        // Jeśli któryś z zasobów został w międzyczasie usunięty, przerywamy akcję
        if (!$book || !$user || !$employee) {
            return;
        }

        $history = new LoanHistory($book, $user, $employee, LoanHistory::ACTION_RETURN);

        $this->loanHistoryRepository->save($history);

        $this->logger->debug(sprintf(
            'Książka o ID %d została zwrócona przez czytelnika o ID %d przez pracownika o ID %d.',
            $book->getId(),
            $user->getId(),
            $employee->getId()
        ));
    }
}
