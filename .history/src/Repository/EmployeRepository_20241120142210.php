<?php

namespace App\Repository;

use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employe>
 */
class EmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    /**
     * Retourne les employés actifs
     *
     * @return Employe[] Returns an array of active Employe objects
     */
    public function findActiveEmployes(): array
    {
        return $this->createQueryBuilder('e') // 'e' est un alias pour Employe
            ->where('e.statut = :statut')
            ->setParameter('statut', true) // Statut actif (true)
            ->orderBy('e.nom', 'ASC') // Trie les employés par nom (ordre croissant)
            ->getQuery()
            ->getResult(); // Retourne le résultat sous forme de tableau
    }

    /**
     * Retourne les employés récents
     *
     * @return Employe[] Returns an array of recent Employe objects
     */
    public function findRecentEmployes(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'DESC') // Trie par date de création (ordre décroissant)
            ->setMaxResults(10) // Limite à 10 résultats
            ->
    }