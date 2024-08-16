<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Rubrik;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByRubrik(Rubrik $rubrik): array
            {
            return $this->createQueryBuilder('p')
            ->andWhere('p.rubrik = :rubrik')
            ->setParameter('rubrik', $rubrik)
            ->getQuery()
            ->getResult();
        }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
