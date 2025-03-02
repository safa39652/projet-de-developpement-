<?php

namespace App\Repository;

use App\Entity\Pointage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Driver\Statement;


/**
 * @extends ServiceEntityRepository<Pointage>
 */
class PointageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pointage::class);
    }
   

    public function findByEmployeAndDateRange(string $employe, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.employe = :employe')
            ->andWhere('p.heureEntree BETWEEN :start AND :end')
            ->setParameter('employe', $employe)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('p.heureEntree', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findThisWeek()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.date BETWEEN :startOfWeek AND :endOfWeek')
           ->setParameter('startOfWeek', new \DateTime('monday this week'))
           ->setParameter('endOfWeek', new \DateTime('sunday this week'));
        
        return $qb->getQuery()->getResult();
    }

    public function findThisMonth()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.date BETWEEN :startOfMonth AND :endOfMonth')
           ->setParameter('startOfMonth', new \DateTime('first day of this month'))
           ->setParameter('endOfMonth', new \DateTime('last day of this month'));
        
        return $qb->getQuery()->getResult();
    }
}




    //    /**
    //     * @return Pointage[] Returns an array of Pointage objects
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

    //    public function findOneBySomeField($value): ?Pointage
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

