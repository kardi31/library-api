<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ApiResource(operations: [])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 6, unique: true)]
    #[Groups(['book:read'])]
    private string $cardNumber;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read'])]
    private string $name;

    #[ORM\OneToMany(targetEntity: LoanHistory::class, mappedBy: 'user', fetch: 'EXTRA_LAZY')]
    private Collection $loanHistories;


    public function __construct(string $cardNumber, string $name)
    {
        $this->cardNumber = $cardNumber;
        $this->name = $name;
        $this->loanHistories = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(string $number): self
    {
        $this->cardNumber = $number;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLoanHistories(): Collection
    {
        return $this->loanHistories;
    }

    public function setLoanHistories(Collection $loanHistories): void
    {
        $this->loanHistories = $loanHistories;
    }
}
