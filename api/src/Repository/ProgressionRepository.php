<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Progression;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Progression>
 *
 * @method Progression|null find($id, $lockMode = null, $lockVersion = null)
 * @method Progression|null findOneBy(array $criteria, array $orderBy = null)
 * @method Progression[]    findAll()
 * @method Progression[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgressionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Progression::class);
    }

    public function save(Progression $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Progression $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByProgression($user, $courseId): ?Progression
    {
        $entityManager = $this->getEntityManager();
    
        $query = $entityManager->createQuery(
            'SELECT p, c, u
            FROM App\Entity\Progression p
            INNER JOIN p.course c
            INNER JOIN p.user u
            WHERE c.id = :courseId
            AND u.id = :userId'
        )   
            ->setParameter('userId', $user)
            ->setParameter('courseId', $courseId);
    
        return $query->getOneOrNullResult();
    }


    public function findByProgressionProf($userId): array
    {
        $entityManager = $this->getEntityManager();
    
        $query = $entityManager->createQuery(
            'SELECT p, c
            FROM App\Entity\Progression p
            INNER JOIN p.course c
            WHERE c.professor = :profId'
        )
        ->setParameter('profId', $userId);
    
        return $query->getResult();
    }



    public function findByProgressionStudents($studentsId): array
    {
        $entityManager = $this->getEntityManager();
    
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Progression p
            INNER JOIN p.user u
            WHERE p.user = :userId'
        )
            ->setParameter('userId', $studentsId);
    
        return $query->getResult();
    }


    public function findByCriteria($user, $title, $status): array
    {
        $qb = $this->createQueryBuilder('p');
    
        if ($user !== null) {
            $qb->join('p.user', 'u')
               ->andWhere('u.id = :user')
               ->setParameter('user', $user);
        }
    
        if ($title !== null) {
            $qb->join('p.course', 'c')
               ->andWhere('c.title LIKE :courseTitle')
               ->setParameter('courseTitle', $title . '%');
        }

        if ($status !== null) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }
    
        return $qb->orderBy('p.id', 'DESC')
                  ->getQuery()
                  ->getResult();
    }


//    /**
//     * @return Progression[] Returns an array of Progression objects
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

//    public function findOneBySomeField($value): ?Progression
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
