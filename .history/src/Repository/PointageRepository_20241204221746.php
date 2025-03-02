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

        // Utilisation de la fonction DAYOFWEEK() pour extraire le jour de la semaine de 'heureEntree'
        $qb->where('DAYOFWEEK(p.heureEntree) = :day')
           ->setParameter('day', $day); // Le paramètre 'day' doit être un nombre entre 1 et 7 (1 = dimanche, 2 = lundi, etc.)

        return $qb->getQuery()->getResult();  // Retourner les résultats
    }
    public function countPointagesByDay($day)
{
    $qb = $this->createQueryBuilder('p');

    // Utilisation de la fonction DAYOFWEEK() pour compter les pointages pour un jour spécifique
    $qb->select('COUNT(p.id)')
       ->where('DAYOFWEEK(p.heureEntree) = :day')
       ->setParameter('day', $day);

    return $qb->getQuery()->getSingleScalarResult();  // Retourne le nombre de pointages
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
