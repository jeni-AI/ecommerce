<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\RefStatutProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return list<Product>
     */
    public function findPublished(
        ?string $search = null,
        ?int $categoryId = null,
        ?int $brandId = null,
        ?int $minimumPrice = null,
        ?int $maximumPrice = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('product')
            ->innerJoin('product.statusRecord', 'status')
            ->addSelect('status')
            ->innerJoin('product.category', 'category')
            ->addSelect('category')
            ->innerJoin('product.brand', 'brand')
            ->addSelect('brand')
            ->andWhere('status.status = :published')
            ->setParameter('published', RefStatutProduit::PUBLISHED)
            ->orderBy('product.created_at', 'DESC');

        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere('LOWER(product.name) LIKE :search OR LOWER(product.description) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        if ($categoryId !== null) {
            $queryBuilder
                ->andWhere('category.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($brandId !== null) {
            $queryBuilder
                ->andWhere('brand.id = :brandId')
                ->setParameter('brandId', $brandId);
        }

        if ($minimumPrice !== null) {
            $queryBuilder
                ->andWhere('product.price >= :minimumPrice')
                ->setParameter('minimumPrice', $minimumPrice);
        }

        if ($maximumPrice !== null) {
            $queryBuilder
                ->andWhere('product.price <= :maximumPrice')
                ->setParameter('maximumPrice', $maximumPrice);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPublishedById(int $id): ?Product
    {
        return $this->createQueryBuilder('product')
            ->innerJoin('product.statusRecord', 'status')
            ->addSelect('status')
            ->innerJoin('product.category', 'category')
            ->addSelect('category')
            ->innerJoin('product.brand', 'brand')
            ->addSelect('brand')
            ->andWhere('product.id = :id')
            ->andWhere('status.status = :published')
            ->setParameter('id', $id)
            ->setParameter('published', RefStatutProduit::PUBLISHED)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
