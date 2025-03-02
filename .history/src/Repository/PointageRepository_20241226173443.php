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
      

    // PointageRepository.php
public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
{
    return $this->createQueryBuilder('p')
        ->andWhere('p.heureEntree BETWEEN :start AND :end')
        ->setParameter('start', $startDate->format('Y-m-d 00:00:00'))
        ->setParameter('end', $endDate->format('Y-m-d 23:59:59'))
        ->getQuery()
        ->getResult();
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

