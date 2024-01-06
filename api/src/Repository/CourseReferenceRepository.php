<?php

namespace App\Repository;

use App\Entity\CourseReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CourseReference>
 *
 * @method CourseReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseReference[]    findAll()
 * @method CourseReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseReference::class);
    }

    public function findAllWithCourse(): array
    {
        return $this->createQueryBuilder('cr')
            ->addSelect('course')  // Ensure that "course" is selected
            ->leftJoin('cr.course', 'course')
            ->getQuery()
            ->getResult();
    }


    public function findByCourseId($courseId)
    {
        return $this->createQueryBuilder('cr')
            ->andWhere('cr.course = :courseId')
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return CourseReference[] Returns an array of CourseReference objects
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

//    public function findOneBySomeField($value): ?CourseReference
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
