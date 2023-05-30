<?php

namespace App\Repository;

use App\Entity\BookReviews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookReviews>
 *
 * @method BookReviews|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookReviews|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookReviews[]    findAll()
 * @method BookReviews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookReviewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookReviews::class);
    }

    public function save(BookReviews $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookReviews $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    //     * @return BookReviews[] Returns an array of BookReviews objects
    //     */
    public function findLatest(int $number): array
    {
        return $this->findBy([], ['created_at' => 'DESC'], $number);
    }

    public function findByUser(int $userId): array
    {
        return $this->findBy(['user_id' => $userId]);
    }

//    /**
//     * @return BookReviews[] Returns an array of BookReviews objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BookReviews
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
