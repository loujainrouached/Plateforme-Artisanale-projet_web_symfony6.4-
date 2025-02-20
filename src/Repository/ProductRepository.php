<?php

namespace App\Repository;

use App\Entity\Product;
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

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    

    public function findAllCategories(): array
{
    return $this->createQueryBuilder('p')
        ->select('DISTINCT p.category')
        ->getQuery()
        ->getSingleColumnResult(); 
}
public function findProductsByUser($userId)
{
    
    return $this->createQueryBuilder('p')
        ->select('p.id, p.name, p.description, p.price, p.stock, p.category, p.image') // Ajouter id dans la sélection
        ->where('p.user = :userId')
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getResult(); // Retourne un tableau associatif
}




}
