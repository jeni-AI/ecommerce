<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
#[ORM\Table(name: 'panier')]
#[ORM\UniqueConstraint(name: 'UNIQ_PANIER_CLIENT', columns: ['client_id'])]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'panier')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Client $client = null;

    /**
     * @var Collection<int, LignePanier>
     */
    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: LignePanier::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignes;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;
        $client->setPanier($this);

        return $this;
    }

    /**
     * @return Collection<int, LignePanier>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LignePanier $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setPanier($this);
            $this->updateTimestamp();
        }

        return $this;
    }

    public function removeLigne(LignePanier $ligne): static
    {
        if ($this->lignes->removeElement($ligne) && $ligne->getPanier() === $this) {
            $ligne->setPanier(null);
            $this->updateTimestamp();
        }

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

    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
