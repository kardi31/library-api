<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(name: 'employees')]
#[ApiResource(operations: [])]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 100)]
    private string $role;

    #[ORM\OneToMany(targetEntity: LoanHistory::class, mappedBy: 'employee', fetch: 'EXTRA_LAZY')]
    private Collection $loanHistories;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
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
