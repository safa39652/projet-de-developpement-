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
   

    public function getPointagesByDay($day)
    {
        $qb = $this->createQueryBuilder('p');

        // Nous appelons DAYOFWEEK() sur 'p.heureEntree' pour obtenir le numéro du jour de la semaine
        $qb->where('DAYOFWEEK(p.heureEntree) = :day')
           ->setParameter('day', $day); // $day est le numéro du jour (1 pour dimanche, 2 pour lundi, etc.)

        return $qb->getQuery()->getResult(); // Récupération des résultats
    }
    public function getDayOfWeek($day)
{
    $daysOfWeek = [
        'Sunday' => 1,
        'Monday' => 2,
        'Tuesday' => 3,
        'Wednesday' => 4,
        'Thursday' => 5,
        'Friday' => 6,
        'Saturday' => 7,
    ];

    return isset($daysOfWeek[$day]) ? $daysOfWeek[$day] : null;  // Retourne le numéro du jour
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
