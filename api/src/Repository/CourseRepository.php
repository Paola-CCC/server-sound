<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Composer;
use App\Entity\Course;
use App\Entity\User;
use App\Entity\Instrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 *
 * @method Course|null find($id, $lockMode = null, $lockVersion = null)
 * @method Course|null findOneBy(array $criteria, array $orderBy = null)
 * @method Course[]    findAll()
 * @method Course[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function save(Course $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Course $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findByProf(User $user)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.professor', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findByInstrument(Instrument $instrument)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.instrument', 'u')
            ->where('u = :instrument')
            ->setParameter('instrument', $instrument)
            ->getQuery()
            ->getResult();
    }


    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.categories', 'k')
            ->Where('k = :category')
            ->setParameter('category', $category)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function findBycomposer(Composer $composer): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.composers', 'compo')
            ->Where('compo = :composer')
            ->setParameter('composer', $composer)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    
    public function findByTitle(string $title = ''): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.title LIKE :title')
            ->setParameter('title', $title . '%')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function findByCriteria( ?User $user , ?Instrument $instrument , ?Category $category, ?Composer $composer , string $title = '' ): array 
    {
        $qb = $this->createQueryBuilder('c');

        if ($user !== null) {
            $qb->join('c.professor', 'u')
                ->andwhere('u = :user')
                ->setParameter('user', $user);
        }

        if ($instrument !== null) {
            $qb->join('c.instrument', 'i')
                ->andwhere('i = :instrument')
                ->setParameter('instrument', $instrument);
        }

        if ($category !== null) {
            $qb->join('c.categories', 'k')
                ->andwhere('k = :category')
                ->setParameter('category', $category);
        }

        if ($composer !== null) {
            $qb->join('c.composers', 'compo')
                ->andwhere('compo = :composer')
                ->setParameter('composer', $composer);
        }

        if ($title !== '') {
            $qb->andWhere('c.title LIKE :title')
                ->setParameter('title', $title . '%');
        }

        return $qb ->orderBy('c.id', 'DESC')
                ->getQuery()
                ->getResult();
    }


//    /**
//     * @return Course[] Returns an array of Course objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Course
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
