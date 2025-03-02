<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Pointage;
use App\Entity\Employe;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EmployeRepository;


class PointageController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    #[Route('/enregistrer-pointage', name: 'enregistrer_pointage', methods: ['POST'])]
public function enregistrerPointage(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $employeNom = $data['employe'] ?? null;
    $statut = $data['statut'] ?? null;
    $photo = $data['photo'] ?? null;

    if (!$employeNom || !$statut) {
        return new JsonResponse(['message' => 'Données invalides'], 400);
    }

    // Récupérer l'employé
    $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $employeNom]);

    if (!$employe) {
        return new JsonResponse(['message' => 'Employé non trouvé'], 404);
    }

    // Vérifier si un pointage ouvert existe (sans heure de sortie)
    $pointageExistant = $entityManager->getRepository(Pointage::class)->findOneBy([
        'employe' => $employeNom,
        'heureSortie' => null
    ]);

    if ($pointageExistant) {
        // Si un pointage ouvert existe, enregistrer l'heure de sortie
        $heureSortie = new \DateTime();
        $pointageExistant->setHeureSortie($heureSortie);

        // Calculer le statut en fonction de l'heure de sortie
        $heureEntreeLimite = new \DateTime('08:00');
        $heureSortieLimite = new \DateTime('17:00');

        $heureEntree = $pointageExistant->getHeureEntree();
        $statutSortie = 'Présence'; // Valeur par défaut

        if ($heureEntree > $heureEntreeLimite) {
            $statutSortie = 'Retard';
        } elseif ($heureSortie < $heureSortieLimite) {
            $statutSortie = 'Sortie avant l\'heure';
        }

        $pointageExistant->setStatut($statutSortie);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Heure de sortie enregistrée avec succès']);
    }

    // Si aucun pointage ouvert, créer un nouveau pointage
    $pointage = new Pointage();
    $pointage->setEmploye($employeNom);
    $pointage->setHeureEntree(new \DateTime());
    $pointage->setStatut($statut);
    $pointage->setPhoto($photo);

    // Calculer le statut pour un nouveau pointage
    $heureEntreeLimite = new \DateTime('08:00');
    $heureSortieLimite = new \DateTime('17:00');

    $heureEntree = $pointage->getHeureEntree();
    $statutEntree = 'Présence'; // Valeur par défaut

    if ($heureEntree > $heureEntreeLimite) {
        $statutEntree = 'Retard';
    }

    $pointage->setStatut($statutEntree);

    $entityManager->persist($pointage);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Pointage enregistré avec succès']);
}

    

    #[Route('/pointage/liste', name: 'pointage_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les pointages
        $pointages = $entityManager->getRepository(Pointage::class)->findAll();

        // Rendre le template avec les données
        return $this->render('pointage/liste.html.twig', [
            'pointages' => $pointages,
        ]);
    }

    #[Route('/filter-pointages', name: 'filter')]
    public function rapportPointages(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mois = $request->query->get('mois');
        $annee = $request->query->get('annee');
        $jour = $request->query->get('jour'); // Jour de la semaine (ex: "Monday")
    
        $qb = $entityManager->getRepository(Pointage::class)->createQueryBuilder('p');
    
        // Appliquer le filtre par mois et année
        if ($mois && $annee) {
            $dateDebut = new \DateTime($annee . '-' . $mois . '-01');
            $dateFin = clone $dateDebut;
            $dateFin->modify('last day of this month');
    
            $qb->andWhere('p.heureEntree BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
        } elseif ($mois) {
            // Filtrer par mois uniquement
            $dateDebut = new \DateTime('first day of ' . $mois . ' month');
            $dateFin = clone $dateDebut;
            $dateFin->modify('last day of this month');
    
            $qb->andWhere('p.heureEntree BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
        } elseif ($annee) {
            // Filtrer par année uniquement
            $dateDebut = new \DateTime($annee . '-01-01');
            $dateFin = new \DateTime($annee . '-12-31');
    
            $qb->andWhere('p.heureEntree BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
        }
    
        // Filtrer par jour de la semaine (ex : 'Monday', 'Tuesday', etc.)
        if ($jour) {
            $jourDeLaSemaine = [
                'Sunday' => 1,
                'Monday' => 2,
                'Tuesday' => 3,
                'Wednesday' => 4,
                'Thursday' => 5,
                'Friday' => 6,
                'Saturday' => 7,
            ];
    
            if (isset($jourDeLaSemaine[$jour])) {
                // Utiliser QueryBuilder pour ajouter la condition de jour de la semaine
                $qb->andWhere('DAYOFWEEK(p.heureEntree) = :jour')
                    ->setParameter('jour', $jourDeLaSemaine[$jour]);
            }
        }
    
        // Exécuter la requête
        $pointages = $qb->getQuery()->getResult();
    
        return $this->render('pointage/liste.html.twig', [
            'pointages' => $pointages,
        ]);
    }
    
}
