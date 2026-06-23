<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\Controller\BorrowBookAction;
use App\Controller\ReturnBookAction;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'books')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['book:read'],
                'swagger_definition_name' => 'Read'
            ]
        ),
        new Post(denormalizationContext: ['groups' => ['book:write']]),
        new Delete(),

        new Post(
            uriTemplate: '/books/{serialNumber}/borrow',
            uriVariables: [
                'serialNumber' => new Link(fromClass: Book::class, identifiers: ['serialNumber'])
            ],
            controller: BorrowBookAction::class,
            openapiContext: [
                'summary' => 'Wypożyczenie książki przez czytelnika.',
                'description' => 'Zmienia status książki na wypożyczoną i przypisuje ją do czytelnika na podstawie numeru karty.',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'card_number' => [
                                        'type' => 'string',
                                        'example' => '123456',
                                        'description' => 'Sześciocyfrowy numer karty bibliotecznej czytelnika'
                                    ]
                                ],
                                'required' => ['card_number']
                            ]
                        ]
                    ]
                ]
            ],
            normalizationContext: ['groups' => ['book:read']],
            name: 'book_borrow'
        ),
        new Post(
            uriTemplate: '/books/{serialNumber}/return',
            uriVariables: [
                'serialNumber' => new Link(fromClass: Book::class, identifiers: ['serialNumber'])
            ],
            controller: ReturnBookAction::class,
            openapiContext: [
                'summary' => 'Zwrot książki do biblioteki.',
                'description' => 'Zdejmuje status wypożyczenia z książki i rejestruje zwrot w historii.',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [],
                            ]
                        ]
                    ]
                ]
            ],
            normalizationContext: ['groups' => ['book:read']],
            name: 'book_return'
        )
    ]
)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 6, unique: true)]
    #[Groups(['book:read', 'book:write'])]
    private string $serialNumber;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write'])]
    private string $title;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write'])]
    private string $author;

    #[ORM\Column]
    #[Groups(['book:read'])]
    private bool $isBorrowed = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'current_borrower_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['book:read'])]
    private ?User $currentBorrower = null;

    #[ORM\OneToMany(targetEntity: LoanHistory::class, mappedBy: 'book', fetch: 'EXTRA_LAZY')]
    private Collection $loanHistories;

    public function __construct(
        string $serialNumber,
        string $title,
        string $author,
        bool $isBorrowed = false,
        ?User $currentBorrower = null
    )
    {
        $this->serialNumber = $serialNumber;
        $this->title = $title;
        $this->author = $author;
        $this->isBorrowed = $isBorrowed;
        $this->currentBorrower = $currentBorrower;
        $this->loanHistories = new ArrayCollection();
    }


    public function borrow(User $user): void
    {
        $this->isBorrowed = true;
        $this->currentBorrower = $user;
    }

    public function returnBook(): void
    {
        $this->isBorrowed = false;
        $this->currentBorrower = null;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    #[Groups(['book:read'])]
    #[SerializedName('isBorrowed')]
    public function isBorrowed(): bool
    {
        return $this->isBorrowed;
    }

    public function getCurrentBorrower(): ?User
    {
        return $this->currentBorrower;
    }

    public function getLoanHistories(): Collection
    {
        return $this->loanHistories;
    }

    public function setLoanHistories(Collection $loanHistories): void
    {
        $this->loanHistories = $loanHistories;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
