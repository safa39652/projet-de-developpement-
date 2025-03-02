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

class PointageController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/enregistrer-pointage', name: 'enregistrer_pointage', methods: ['POST'])]
    
    // src/Controller/PointageController.php
    #[Route('/enregistrer-pointage', name: 'enregistrer_pointage', methods: ['POST'])]
public function enregistrerPointage(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $employeNom = $data['employe'] ?? null;
    $statut = $data['statut'] ?? null;
    $photo = $data['photo'] ?? null; // Récupérer le chemin de l'image

    if (!$employeNom || !$statut) {
        return new JsonResponse(['message' => 'Données invalides'], 400);
    }

    // Chercher l'employé
    $employe = $this->employeRepository->findOneBy(['nom' => $employeNom]);

    if (!$employe) {
        return new JsonResponse(['message' => 'Employé non trouvé'], 404);
    }

    // Créer un nouveau pointage
    $pointage = new Pointage();
    $pointage->setEmploye($employe);
    $pointage->setHeureEntree(new \DateTime());
    $pointage->setStatut($statut);
    $pointage->setPhoto($photo); // Enregistrer le chemin de l'image

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
}
