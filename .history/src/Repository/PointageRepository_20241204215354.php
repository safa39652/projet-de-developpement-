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
    $conn = $this->getEntityManager()->getConnection();
    
    // Utiliser une requête SQL brute pour récupérer les pointages par jour de la semaine
    $sql = "
        SELECT COUNT(p.id)
        FROM pointages p
        WHERE DAYOFWEEK(p.date) = :day
    ";
    
    // Exécuter la requête avec le jour passé en paramètre
    $stmt = $conn->prepare($sql);
    $stmt->execute(['day' => $this->getDayOfWeek($day)]);
    
    return $stmt->fetchColumn();
}

private function getDayOfWeek($day)
{
    $days = [
        'Sunday' => 1,
        'Monday' => 2,
        'Tuesday' => 3,
        'Wednesday' => 4,
        'Thursday' => 5,
        'Friday' => 6,
        'Saturday' => 7,
    ];
    
    return $days[$day] ?? 1; // Retourner 1 (dimanche) par défaut
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
