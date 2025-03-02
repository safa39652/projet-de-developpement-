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
    $photo = $data['photo'] ?? null;

    if (!$employeNom) {
        return new JsonResponse(['message' => 'Données invalides'], 400);
    }

    // Récupérer l'employé
    $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $employeNom]);

    if (!$employe) {
        return new JsonResponse(['message' => 'Employé non trouvé'], 404);
    }

    // Vérifier s'il existe un pointage sans heure de sortie pour cet employé
    $pointage = $entityManager->getRepository(Pointage::class)
                              ->findOneBy(['employe' => $employeNom, 'heureSortie' => null]);

    if ($pointage) {
        // Si un pointage existe, enregistrer l'heure de sortie
        $pointage->setHeureSortie(new \DateTime());

        // Calculer le statut en fonction des heures d'entrée et de sortie
        $heureEntreeLimite = new \DateTime('08:00');
        $heureSortieLimite = new \DateTime('17:00');

        if ($pointage->getHeureEntree() > $heureEntreeLimite) {
            $pointage->setStatut('Retard');
        } elseif ($pointage->getHeureSortie() < $heureSortieLimite) {
            $pointage->setStatut('Sortie avant l\'heure');
        } else {
            $pointage->setStatut('Présence');
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Heure de sortie enregistrée avec succès']);
    } else {
        // Sinon, créer un nouveau pointage pour l'heure d'entrée
        $pointage = new Pointage();
        $pointage->setEmploye($employeNom);
        $pointage->setHeureEntree(new \DateTime());
        $pointage->setPhoto($photo);

        // Calculer le jour de la semaine
        $jour = (new \DateTime())->format('l'); // Exemple : "Monday"
        $pointage->setJour($jour);

        $entityManager->persist($pointage);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Pointage enregistré avec succès']);
    }
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
}
