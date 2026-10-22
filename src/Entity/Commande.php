<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    public const PAYMENT_PENDING = 'non_paye';
    public const PAYMENT_PAID = 'paye';
    public const PAYMENT_REFUNDED = 'rembourse';
    public const METHOD_NOT_SPECIFIED = 'non_renseigne';
    public const METHOD_AIRTEL_MONEY = 'airtel_money';
    public const METHOD_MOOV_MONEY = 'moov_money';
    public const METHOD_BANK_TRANSFER = 'virement_bancaire';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32, unique: true)]
    private string $code;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $totalAmount = 0;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::PAYMENT_PENDING,
        self::PAYMENT_PAID,
        self::PAYMENT_REFUNDED,
    ])]
    private string $paymentStatus = self::PAYMENT_PENDING;

    #[ORM\Column(length: 30, options: ['default' => self::METHOD_NOT_SPECIFIED])]
    #[Assert\Choice(choices: [
        self::METHOD_NOT_SPECIFIED,
        self::METHOD_AIRTEL_MONEY,
        self::METHOD_MOOV_MONEY,
        self::METHOD_BANK_TRANSFER,
    ])]
    private string $paymentMethod = self::METHOD_NOT_SPECIFIED;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $deliveryPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryAddressComplement = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $deliveryCity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $deliveryPostalCode = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $deliveryCountry = null;

    #[ORM\OneToOne(mappedBy: 'commande', targetEntity: RefStatutCmd::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?RefStatutCmd $statusRecord = null;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: LigneCommande::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignes;

    public function __construct()
    {
        $this->code = 'CMD-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
        $this->createdAt = new \DateTimeImmutable();
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(int $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getStatusRecord(): ?RefStatutCmd
    {
        return $this->statusRecord;
    }

    public function setStatusRecord(?RefStatutCmd $status): static
    {
        if ($this->statusRecord === $status) {
            return $this;
        }

        $previousStatus = $this->statusRecord;
        $this->statusRecord = $status;
        if ($previousStatus !== null && $previousStatus->getCommande() === $this) {
            $previousStatus->setCommande(null);
        }
        if ($status !== null && $status->getCommande() !== $this) {
            $status->setCommande($this);
        }

        return $this;
    }

    public function getCurrentStatus(): ?RefStatutCmd
    {
        return $this->statusRecord;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getDeliveryPhone(): ?string
    {
        return $this->deliveryPhone;
    }

    public function setDeliveryPhone(?string $deliveryPhone): static
    {
        $this->deliveryPhone = $deliveryPhone;

        return $this;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?string $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getDeliveryAddressComplement(): ?string
    {
        return $this->deliveryAddressComplement;
    }

    public function setDeliveryAddressComplement(?string $deliveryAddressComplement): static
    {
        $this->deliveryAddressComplement = $deliveryAddressComplement;

        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(?string $deliveryCity): static
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }

    public function getDeliveryPostalCode(): ?string
    {
        return $this->deliveryPostalCode;
    }

    public function setDeliveryPostalCode(?string $deliveryPostalCode): static
    {
        $this->deliveryPostalCode = $deliveryPostalCode;

        return $this;
    }

    public function getDeliveryCountry(): ?string
    {
        return $this->deliveryCountry;
    }

    public function setDeliveryCountry(?string $deliveryCountry): static
    {
        $this->deliveryCountry = $deliveryCountry;

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function getLignesTotalAmount(): int
    {
        return array_reduce(
            $this->lignes->toArray(),
            static fn (int $total, LigneCommande $ligne): int => $total + $ligne->getSubtotal(),
            0
        );
    }

    public function addLigne(LigneCommande $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setCommande($this);
        }

        return $this;
    }

    public function removeLigne(LigneCommande $ligne): static
    {
        if ($this->lignes->removeElement($ligne) && $ligne->getCommande() === $this) {
            $ligne->setCommande(null);
        }

        return $this;
    }
}
