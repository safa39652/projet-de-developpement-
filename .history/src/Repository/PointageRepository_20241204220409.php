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
    namespace App\Repository;

    use Doctrine\DBAL\Driver\Connection;
    use Doctrine\ORM\EntityRepository;
    
    class PointageRepository extends EntityRepository
    {
        public function getPointagesByDay($day)
        {
            // Connexion DBAL
            $conn = $this->getEntityManager()->getConnection();
            
            // Requête SQL
            $sql = "SELECT * FROM pointages WHERE DAYOFWEEK(heureEntree) = :day";
            
            // Exécuter la requête
            $stmt = $conn->prepare($sql);
            $stmt->execute(['day' => $this->getDayOfWeek($day)]);
            
            // Retourner tous les résultats
            return $stmt->fetchAll();  // Ici, fetchAll() est valable car on travaille avec DBAL
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
