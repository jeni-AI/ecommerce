<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brand $brand = null;

    #[ORM\OneToOne(mappedBy: 'product', targetEntity: RefStatutProduit::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?RefStatutProduit $statusRecord = null;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: LigneCommande::class)]
    private Collection $lignesCommande;

    /**
     * @var Collection<int, LignePanier>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: LignePanier::class)]
    private Collection $lignesPanier;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->lignesCommande = new ArrayCollection();
        $this->lignesPanier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getStatusRecord(): ?RefStatutProduit
    {
        return $this->statusRecord;
    }

    public function setStatusRecord(?RefStatutProduit $status): static
    {
        if ($this->statusRecord === $status) {
            return $this;
        }

        $previousStatus = $this->statusRecord;
        $this->statusRecord = $status;
        if ($previousStatus !== null && $previousStatus->getProduct() === $this) {
            $previousStatus->setProduct(null);
        }
        if ($status !== null && $status->getProduct() !== $this) {
            $status->setProduct($this);
        }

        return $this;
    }

    public function getCurrentStatus(): ?RefStatutProduit
    {
        return $this->statusRecord;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLignesCommande(): Collection
    {
        return $this->lignesCommande;
    }

    public function addLigneCommande(LigneCommande $ligne): static
    {
        if (!$this->lignesCommande->contains($ligne)) {
            $this->lignesCommande->add($ligne);
            $ligne->setProduct($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligne): static
    {
        if ($this->lignesCommande->removeElement($ligne) && $ligne->getProduct() === $this) {
            $ligne->setProduct(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, LignePanier>
     */
    public function getLignesPanier(): Collection
    {
        return $this->lignesPanier;
    }

    public function addLignePanier(LignePanier $ligne): static
    {
        if (!$this->lignesPanier->contains($ligne)) {
            $this->lignesPanier->add($ligne);
            $ligne->setProduct($this);
        }

        return $this;
    }

    public function removeLignePanier(LignePanier $ligne): static
    {
        if ($this->lignesPanier->removeElement($ligne) && $ligne->getProduct() === $this) {
            $ligne->setProduct(null);
        }

        return $this;
    }
}
