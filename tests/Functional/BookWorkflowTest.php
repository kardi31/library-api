<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Book;
use App\Entity\Employee;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class BookWorkflowTest extends ApiTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $client = self::createClient();
        $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        // Izolacja bazy danych
        $this->entityManager->createQuery('DELETE FROM App\Entity\LoanHistory')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Book')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Employee')->execute();
    }

    /**
     * SCENARIUSZ 1: Pełna ścieżka sukcesu (Happy Path) + Blokada podwójnego wypożyczenia
     */
    public function testFullBookBorrowAndReturnWorkflow(): void
    {
        $client = self::createClient();

        $user = new User('111222', 'Testowy Czytelnik');
        $this->entityManager->persist($user);

        $book = new Book('TEST01', 'Testowanie API', 'Jan Tester', false, null);
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $employee = new Employee();
        $employee->setName('Testowy Bibliotekarz');
        $employee->setRole('Moderator');
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
        // 1. Wypożyczenie
        $client->request('POST', '/api/books/TEST01/borrow', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['card_number' => '111222']
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'serialNumber' => 'TEST01',
            'isBorrowed' => true,
            'currentBorrower' => [
                'cardNumber' => '111222',
                'name' => 'Testowy Czytelnik'
            ]
        ]);

        // 2. Próba ponownego wypożyczenia (Oczekiwany błąd 400)
        $client->request('POST', '/api/books/TEST01/borrow', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['card_number' => '111222']
        ]);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains([
            'hydra:description' => 'Książka została już wypożyczona.',
        ]);

        // 3. Zwrot książki
        $client->request('POST', '/api/books/TEST01/return', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => []
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'serialNumber' => 'TEST01',
            'isBorrowed' => false
        ]);
    }

    public function testCannotBorrowNonExistentBook(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/books/FAKE99/borrow', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['card_number' => '111222']
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * SCENARIUSZ 3: Próba zwrotu książki, która NIE JEST wypożyczona (Oczekiwany błąd 400)
     */
    public function testCannotReturnBookThatIsAlreadyInLibrary(): void
    {
        $client = self::createClient();

        // Tworzymy książkę, która leży na półce (isBorrowed = false)
        $book = new Book('TEST02', 'Darmowa Książka', 'Autor', false, null);
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $client->request('POST', '/api/books/TEST02/return', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(400);
        // Dopasuj poniższy komunikat do tego, co dokładnie zwraca Twój ReturnBookAction
        $this->assertJsonContains([
            'hydra:description' => 'Operacja niemożliwa do wykonania.',
        ]);
    }
}
