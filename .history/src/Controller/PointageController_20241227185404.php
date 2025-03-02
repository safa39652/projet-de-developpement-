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
    public function enregistrerPointage(Request $request, EntityManagerInterface $entityManager, string $uploadsDir): JsonResponse
    {
        $data = $request->request->all();
        $photoFile = $request->files->get('photo'); // Récupérer le fichier image
    
        $employeNom = $data['employe'] ?? null;
    
        if (!$employeNom) {
            return new JsonResponse(['message' => 'Données invalides'], 400);
        }
    
        // Récupérer l'employé
        $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $employeNom]);
    
        if (!$employe) {
            return new JsonResponse(['message' => 'Employé non trouvé'], 404);
        }
    
        // Vérifier s'il existe un pointage sans heure de sortie
        $pointage = $entityManager->getRepository(Pointage::class)
                                  ->findOneBy(['employe' => $employeNom, 'heureSortie' => null]);
    
        if ($pointage) {
            // Si un pointage existe, enregistrer l'heure de sortie
            $pointage->setHeureSortie(new \DateTime());
            $entityManager->flush();
    
            return new JsonResponse(['message' => 'Heure de sortie enregistrée avec succès']);
        } else {
            // Créer un nouveau pointage
            $pointage = new Pointage();
            $pointage->setEmploye($employeNom);
            $pointage->setHeureEntree(new \DateTime());
    
            // Gérer l'image si elle est envoyée
            if ($photoFile) {
                // Définir un chemin unique pour l'image
                $uniqueFilename = uniqid() . '.' . $photoFile->guessExtension();
                $photoFile->move($uploadsDir, $uniqueFilename); // Déplacer le fichier dans le dossier défini
    
                $photoPath = 'uploads/pointages/' . $uniqueFilename; // Générer le chemin relatif
                $pointage->setPhoto($photoPath); // Enregistrer le chemin dans la base
            }
    
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
