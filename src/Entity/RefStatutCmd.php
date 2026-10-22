<?php

namespace App\Entity;

use App\Repository\RefStatutCmdRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RefStatutCmdRepository::class)]
#[ORM\Table(name: 'ref_statut_cmd', uniqueConstraints: [new ORM\UniqueConstraint(name: 'UNIQ_REF_STATUT_CMD_COMMANDE', columns: ['commande_id'])])]
class RefStatutCmd
{
    public const PENDING = 'en_attente';
    public const VALIDATED = 'validee';
    public const REJECTED = 'non_validee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'statusRecord')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Commande $commande = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::PENDING,
        self::VALIDATED,
        self::REJECTED,
    ])]
    private string $status = self::PENDING;

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
        if ($previousCommande !== null && $previousCommande->getStatusRecord() === $this) {
            $previousCommande->setStatusRecord(null);
        }
        if ($commande !== null && $commande->getStatusRecord() !== $this) {
            $commande->setStatusRecord($this);
        }

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLabel(): string
    {
        return match ($this->status) {
            self::PENDING => 'En attente',
            self::VALIDATED => 'Validée',
            self::REJECTED => 'Non validée',
        };
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
