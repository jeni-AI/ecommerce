<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
#[ORM\Table(name: 'ligne_commande')]
#[ORM\UniqueConstraint(name: 'UNIQ_LIGNE_COMMANDE_PRODUIT', columns: ['commande_id', 'product_id'])]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'lignesCommande')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\PositiveOrZero]
    private int $unitPrice = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        if ($this->commande === $commande) {
            return $this;
        }

        $previousCommande = $this->commande;
        $this->commande = $commande;

        if ($previousCommande !== null) {
            $previousCommande->removeLigne($this);
        }
        if ($commande !== null) {
            $commande->addLigne($this);
        }

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        if ($this->product === $product) {
            return $this;
        }

        $previousProduct = $this->product;
        $this->product = $product;

        if ($previousProduct !== null) {
            $previousProduct->removeLigneCommande($this);
        }
        if ($product !== null) {
            $product->addLigneCommande($this);
        }

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(int $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getSubtotal(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}
