<?php

namespace App\Entity;

use App\Repository\LignePanierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LignePanierRepository::class)]
#[ORM\Table(name: 'ligne_panier')]
#[ORM\UniqueConstraint(name: 'UNIQ_LIGNE_PANIER_PRODUCT', columns: ['panier_id', 'product_id'])]
class LignePanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Panier $panier = null;

    #[ORM\ManyToOne(inversedBy: 'lignesPanier')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Product $product = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantity = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        if ($this->panier === $panier) {
            return $this;
        }

        $previousPanier = $this->panier;
        $this->panier = $panier;

        if ($previousPanier !== null) {
            $previousPanier->removeLigne($this);
        }
        if ($panier !== null) {
            $panier->addLigne($this);
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
            $previousProduct->removeLignePanier($this);
        }
        if ($product !== null) {
            $product->addLignePanier($this);
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
        $this->panier?->updateTimestamp();

        return $this;
    }

    public function getSubtotal(): int
    {
        return $this->quantity * ($this->product?->getPrice() ?? 0);
    }
}
