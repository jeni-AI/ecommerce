<?php

namespace App\Repository;

use App\Entity\RefStatutCmd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefStatutCmd>
 */
class RefStatutCmdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefStatutCmd::class);
    }
}
