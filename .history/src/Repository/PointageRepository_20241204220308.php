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
        // Créez une requête DQL (Doctrine Query Language) qui est l'ORM de Doctrine
        $qb = $this->createQueryBuilder('p');
        
        // Ajoutez une condition pour filtrer par jour de la semaine (heureEntree)
        $qb->where('DAYOFWEEK(p.heureEntree) = :day')
           ->setParameter('day', $this->getDayOfWeek($day));

        // Exécutez la requête et récupérez les résultats
        return $qb->getQuery()->getResult();  // getResult() pour récupérer les résultats avec ORM
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

        return isset($daysOfWeek[$day]) ? $daysOfWeek[$day] : null;
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

        return isset($daysOfWeek[$day]) ? $daysOfWeek[$day] : null;
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
