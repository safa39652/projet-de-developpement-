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

public function enregistrerPointage(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    // Décoder les données reçues
    $data = json_decode($request->getContent(), true);

    // Log pour déboguer les données
    $this->logger->info('Données reçues : ' . json_encode($data));

    // Vérification des données nécessaires
    if (!isset($data['employe']) || !isset($data['statut'])) {
        return new JsonResponse(['message' => 'Données invalides'], 400);
    }

    // Vérification si l'employé existe dans la base de données
    $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $data['employe']]);

    if (!$employe) {
        return new JsonResponse(['message' => 'Employé inconnu'], 404);
    }

    // Gestion de l'image
    $photo = $request->files->get('photo');
    $photoPath = null;

    if ($photo) {
        $uploadsDir = $this->getParameter('uploads_directory');
        $filename = uniqid() . '.' . $photo->guessExtension();
        $photo->move($uploadsDir, $filename);
        $photoPath = '/uploads/' . $filename;
    }

    // Création du pointage
    $pointage = new Pointage();
    $pointage->setEmploye($data['employe']);
    $pointage->setHeureEntree(new \DateTime());
    $pointage->setStatut($data['statut']);
    $pointage->setPhoto($photoPath);

    // Calculer le jour de la semaine
    $jour = (new \DateTime())->format('l');
    $pointage->setJour($jour);

    $entityManager->persist($pointage);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Pointage enregistré avec succès !']);
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
