<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class BookReturnedEvent extends Event
{
    public const string NAME = 'book.returned';

    public function __construct(
        private readonly int $bookId,
        private readonly int $userId,
        private readonly int $employeeId
    ) {}

    public function getEmployeeId(): int
    {
        return $this->employeeId;
    }

    public function getBookId(): int
    {
        return $this->bookId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
