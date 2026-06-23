<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Book;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    public function testInitialStateOfBook(): void
    {
        $book = new Book('UNIT01', 'Czysty Kod', 'Robert C. Martin');

        $this->assertSame('UNIT01', $book->getSerialNumber());
        $this->assertSame('Czysty Kod', $book->getTitle());
        $this->assertSame('Robert C. Martin', $book->getAuthor());
        $this->assertFalse($book->isBorrowed());
        $this->assertNull($book->getCurrentBorrower());
    }

    public function testBorrowChangesStateCorrectly(): void
    {
        $book = new Book('UNIT01', 'Czysty Kod', 'Robert C. Martin');
        $user = $this->createMock(User::class);

        $book->borrow($user);

        $this->assertTrue($book->isBorrowed());
        $this->assertSame($user, $book->getCurrentBorrower());
    }

    public function testReturnChangesStateCorrectly(): void
    {
        $book = new Book('UNIT01', 'Czysty Kod', 'Robert C. Martin');
        $user = $this->createMock(User::class);

        // Najpierw wypożyczamy, potem zwracamy
        $book->borrow($user);
        $book->returnBook();

        $this->assertFalse($book->isBorrowed());
        $this->assertNull($book->getCurrentBorrower());
    }
}
