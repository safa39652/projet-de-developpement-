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
    ublic function getPointagesByDay($day)
    {
        // Connexion à la base de données via Doctrine DBAL
        $conn = $this->getEntityManager()->getConnection();

        // Requête SQL
        $sql = "
            SELECT p.id, p.heureEntree
            FROM pointages p
            WHERE DAYOFWEEK(p.heureEntree) = :day
        ";

        // Préparation et exécution de la requête
        $stmt = $conn->prepare($sql);
        $stmt->execute(['day' => $this->getDayOfWeek($day)]);
        
        // Récupération des résultats sous forme de tableau associatif
        return $stmt->fetchAll(); // Assurez-vous d'importer la classe Statement
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
