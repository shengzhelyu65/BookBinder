<?php

namespace App\Repository;

use App\Entity\MeetupRequestList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MeetupRequestList>
 *
 * @method MeetupRequestList|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetupRequestList|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetupRequestList[]    findAll()
 * @method MeetupRequestList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetupRequestListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeetupRequestList::class);
    }

    public function save(MeetupRequestList $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MeetupRequestList $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MeetupRequestList[] Returns an array of MeetupRequestList objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MeetupRequestList
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
