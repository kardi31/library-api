<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LoanHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoanHistoryRepository::class)]
#[ORM\Table(name: 'loan_histories')]
#[ApiResource(operations: [])]
class LoanHistory
{
    public const string ACTION_BORROW = 'borrow';
    public const string ACTION_RETURN = 'return';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Book $book;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Employee $employee;

    #[ORM\Column(length: 20)]
    private string $action;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(Book $book, User $user, Employee $employee, string $action)
    {
        $this->book = $book;
        $this->user = $user;
        $this->employee = $employee;
        $this->action = $action;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
