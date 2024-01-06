<?php

namespace App\Repository;

use App\Entity\HistoQuizz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoQuizz>
 *
 * @method HistoQuizz|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoQuizz|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoQuizz[]    findAll()
 * @method HistoQuizz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoQuizzRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoQuizz::class);
    }

    public function save(HistoQuizz $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HistoQuizz $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HistoQuizz[] Returns an array of HistoQuizz objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HistoQuizz
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
