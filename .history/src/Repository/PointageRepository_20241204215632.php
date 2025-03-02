<?php

namespace App\Repository;

use App\Entity\Pointage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pointage>
 */
class PointageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pointage::class);
    }
    // src/Repository/PointageRepository.php

public function countByDay($day)
{
    $qb = $this->createQueryBuilder('p')
               ->select('COUNT(p.id)')
               ->where("p.date >= :startDate")
               ->andWhere("p.date <= :endDate")
               ->setParameter('startDate', $this->getStartOfDay($day))
               ->setParameter('endDate', $this->getEndOfDay($day))
               ->getQuery();

    return $qb->getSingleScalarResult();
}

private function getStartOfDay($day)
{
    $date = new \DateTime('this week ' . $day); // Par exemple, 'this week Monday'
    return $date->setTime(0, 0); // Fixer l'heure à 00:00:00
}

private function getEndOfDay($day)
{
    $date = new \DateTime('this week ' . $day);
    return $date->setTime(23, 59, 59); // Fixer l'heure à 23:59:59
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
}
