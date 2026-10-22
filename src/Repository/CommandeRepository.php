<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * @return list<Commande>
     */
    public function findForClient(User $client): array
    {
        return $this->createQueryBuilder('commande')
            ->leftJoin('commande.statusRecord', 'status')
            ->addSelect('status')
            ->andWhere('commande.client = :client')
            ->setParameter('client', $client)
            ->orderBy('commande.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
