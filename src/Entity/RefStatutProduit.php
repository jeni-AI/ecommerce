<?php

namespace App\Entity;

use App\Repository\RefStatutProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RefStatutProduitRepository::class)]
#[ORM\Table(name: 'ref_statut_produit', uniqueConstraints: [new ORM\UniqueConstraint(name: 'UNIQ_REF_STATUT_PRODUIT_PRODUCT', columns: ['product_id'])])]
class RefStatutProduit
{
    public const HIDDEN = 'masque';
    public const PUBLISHED = 'publie';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'statusRecord')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Product $product = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::HIDDEN, self::PUBLISHED])]
    private string $status = self::HIDDEN;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        if ($previousProduct !== null && $previousProduct->getStatusRecord() === $this) {
            $previousProduct->setStatusRecord(null);
        }
        if ($product !== null && $product->getStatusRecord() !== $this) {
            $product->setStatusRecord($this);
        }

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLabel(): string
    {
        return $this->status === self::PUBLISHED ? 'Publié' : 'Masqué';
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
}
