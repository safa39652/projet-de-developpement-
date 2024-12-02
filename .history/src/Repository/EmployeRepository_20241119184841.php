<?php

namespace App\Repository;

use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\findActiveEmployes;
/**
 * @extends ServiceEntityRepository<Employe>
 */
class EmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    //    /**
    //     * @return Employe[] Returns an array of Employe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Employe
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
  
    //    }



    public function findActiveEmployes(): array
    {
        return $this->createQueryBuilder('e') // 'e' est un alias pour Employe
            ->where('e.statut = :statut')
            ->setParameter('statut', true)  // Active = true
            ->orderBy('e.nom', 'ASC') // Trie les employés par nom, ordre croissant
            ->getQuery()
            ->getResult(); // Exécute la requête et retourne le résultat sous forme de tableau
    }


    public function findRecentEmployes(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }


  
}



