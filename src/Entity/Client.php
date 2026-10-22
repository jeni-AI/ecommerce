<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'client')]
#[ORM\UniqueConstraint(name: 'UNIQ_CLIENT_USER', columns: ['user_id'])]
#[ORM\HasLifecycleCallbacks]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'client')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $personalAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $personalAddressComplement = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $personalCity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $personalPostalCode = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $personalCountry = null;

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

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(mappedBy: 'client', targetEntity: Panier::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Panier $panier = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        if ($this->user !== $user) {
            $this->user = $user;
            $user->setClient($this);
        }

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPersonalAddress(): ?string
    {
        return $this->personalAddress;
    }

    public function setPersonalAddress(?string $personalAddress): static
    {
        $this->personalAddress = $personalAddress;

        return $this;
    }

    public function getPersonalAddressComplement(): ?string
    {
        return $this->personalAddressComplement;
    }

    public function setPersonalAddressComplement(?string $personalAddressComplement): static
    {
        $this->personalAddressComplement = $personalAddressComplement;

        return $this;
    }

    public function getPersonalCity(): ?string
    {
        return $this->personalCity;
    }

    public function setPersonalCity(?string $personalCity): static
    {
        $this->personalCity = $personalCity;

        return $this;
    }

    public function getPersonalPostalCode(): ?string
    {
        return $this->personalPostalCode;
    }

    public function setPersonalPostalCode(?string $personalPostalCode): static
    {
        $this->personalPostalCode = $personalPostalCode;

        return $this;
    }

    public function getPersonalCountry(): ?string
    {
        return $this->personalCountry;
    }

    public function setPersonalCountry(?string $personalCountry): static
    {
        $this->personalCountry = $personalCountry;

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;

        return $this;
    }
}
