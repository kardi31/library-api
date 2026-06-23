<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Employee;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user1 = new User('123456', 'Jan Kowalski');
        $user2 = new User('654321', 'Anna Nowak');
        $user3 = new User('987654', 'Piotr Wiśniewski');

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);

        $employee1 = new Employee();
        $employee1->setName('Marek Bibliotekarz');
        $employee1->setRole('Kierownik');

        $employee2 = new Employee();
        $employee2->setName('Ewa Kwiatkowska');
        $employee2->setRole('Młodszy Bibliotekarz');

        $manager->persist($employee1);
        $manager->persist($employee2);

        $book1 = new Book('BK-001', 'Wiedźmin: Ostatnie życzenie', 'Andrzej Sapkowski', false, null);
        $book2 = new Book('BK-002', 'Mistrz i Małgorzata', 'Michaił Bułhakow', false, null);
        $book3 = new Book('BK-003', 'Pragmatyczny programista', 'Andrew Hunt', false, null);

        $book4 = new Book('BK-004', 'Czysty Kod', 'Robert C. Martin', true, $user1);

        $manager->persist($book1);
        $manager->persist($book2);
        $manager->persist($book3);
        $manager->persist($book4);

        $manager->flush();
    }
}
