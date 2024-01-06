<?php

namespace App\Repository;

use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Forum>
 *
 * @method Forum|null find($id, $lockMode = null, $lockVersion = null)
 * @method Forum|null findOneBy(array $criteria, array $orderBy = null)
 * @method Forum[]    findAll()
 * @method Forum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    public function save(Forum $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Forum $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    
    public function findAllSortedByTitle()
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.subject', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategoryId($categoryID): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.category', 'c')
            ->andWhere('c.id = :categoryID')
            ->setParameter('categoryID', $categoryID)
            ->orderBy('f.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    
    public function findBySubjectName(string $subjectForum = ''): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.subject LIKE :subject')
            ->setParameter('subject', $subjectForum . '%') // Ajoutez un % pour chercher les correspondances de début
            ->orderBy('f.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Forum[] Returns an array of Forum objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Forum
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
