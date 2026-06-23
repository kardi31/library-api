<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\LoanHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LoanHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoanHistory::class);
    }

    public function save(LoanHistory $book, bool $flush = true): void
    {
        $this->getEntityManager()->persist($book);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
